<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\ContractInstallmentService;
use App\Services\RecurringContractService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    protected function getCurrentCompany(): Company
    {
        $user = Auth::user();
        
        // Super admin não pode acessar rotas de empresa
        if ($user->is_super_admin ?? false) {
            abort(403, 'Super administradores devem usar o painel administrativo.');
        }
        
        $companyId = session('current_company_id');
        if (!$companyId) {
            $company = $user->currentCompany();
            if ($company) {
                session(['current_company_id' => $company->id]);
                return $company;
            }
            abort(403, 'Você não possui uma empresa vinculada.');
        }
        return Company::findOrFail($companyId);
    }

    public function index()
    {
        $company = $this->getCurrentCompany();
        $contracts = Contract::where('company_id', $company->id)
            ->with(['client', 'employee'])
            ->latest()
            ->paginate(15);
        
        return view('company.contracts.index', compact('contracts', 'company'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->get();
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->get();
        
        return view('company.contracts.create', compact('company', 'clients', 'employees'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'type' => 'required|in:client_recurring,client_fixed,employee_clt,employee_pj',
            'client_id' => 'required_if:type,client_recurring,client_fixed|nullable|exists:clients,id',
            'employee_id' => 'required_if:type,employee_clt,employee_pj|nullable|exists:employees,id',
            'number' => 'nullable|string|max:100|unique:contracts,number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'has_down_payment' => 'nullable|boolean',
            'down_payment_value' => 'nullable|numeric|min:0',
            'down_payment_date' => 'nullable|date',
            'installments_count' => 'nullable|integer|min:1',
            'payment_frequency' => 'nullable|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'equal_installments' => 'nullable|boolean',
            'first_installment_date' => 'nullable|date',
            'billing_period' => 'nullable|in:monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'auto_renew' => 'nullable|boolean',
            'status' => 'nullable|in:active,suspended,cancelled,expired',
            'notes' => 'nullable|string',
            'preview_installment_values' => 'nullable|array',
            'preview_installment_dates' => 'nullable|array',
            'preview_installment_status' => 'nullable|array',
        ]);

        $validated['company_id'] = $company->id;
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }
        if (!isset($validated['auto_renew'])) {
            $validated['auto_renew'] = false;
        }
        if (!isset($validated['has_down_payment'])) {
            $validated['has_down_payment'] = false;
        }
        if (!isset($validated['equal_installments'])) {
            $validated['equal_installments'] = true;
        }

        // Se for contrato fixo, valida configuração de parcelas
        if ($validated['type'] === 'client_fixed') {
            if (!isset($validated['installments_count']) || $validated['installments_count'] < 1) {
                $validated['installments_count'] = 1;
            }
            if (!isset($validated['payment_frequency'])) {
                $validated['payment_frequency'] = 'monthly';
            }
            if (!isset($validated['first_installment_date'])) {
                $validated['first_installment_date'] = $validated['start_date'];
            }
        }
        
        // Se for contrato recorrente, define valores padrão para data de vencimento
        if ($validated['type'] === 'client_recurring' && $validated['billing_period'] === 'monthly') {
            if (!isset($validated['recurring_due_date_type'])) {
                $validated['recurring_due_date_type'] = 'fixed_day';
            }
            if ($validated['recurring_due_date_type'] === 'fixed_day' && !isset($validated['recurring_due_date_day'])) {
                $validated['recurring_due_date_day'] = 5; // Padrão: dia 5
            }
        }

        $contract = Contract::create($validated);

        // Gera parcelas se for contrato fixo
        if ($contract->type === 'client_fixed') {
            $service = new ContractInstallmentService();
            
            // Se há valores do preview, usa eles diretamente
            if ($request->has('preview_installment_values')) {
                $service->generateInstallmentsFromPreview($contract, $request);
            } else {
                $service->generateInstallments($contract);
                
                // Se parcelas não são iguais e há valores manuais, atualiza após gerar
                if (!$contract->equal_installments && $request->has('installment_values')) {
                    $values = $request->input('installment_values', []);
                    $dates = $request->input('installment_dates', []);
                    $service->updateInstallmentValues($contract, $values, $dates);
                }
            }
        }

        return redirect()->route('company.contracts.index')
            ->with('success', 'Contrato criado com sucesso!');
    }

    public function show(Contract $contract)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($contract, $company);
        
        $contract->load('client', 'employee', 'projects', 'installments', 'receivables');
        
        // Estatísticas de receivables
        $totalReceivables = $contract->receivables->sum('value');
        $paidReceivables = $contract->receivables->where('status', 'paid')->sum('value');
        $pendingReceivables = $contract->receivables->where('status', 'pending')->sum('value');
        $partialReceivables = $contract->receivables->where('status', 'partial')->sum(function($r) {
            return $r->value - ($r->paid_value ?? 0);
        });
        $overdueReceivables = $contract->receivables->filter(function($r) {
            return $r->status !== 'paid' && $r->due_date < now();
        })->sum(function($r) {
            return $r->status === 'partial' 
                ? ($r->value - ($r->paid_value ?? 0))
                : $r->value;
        });
        
        $paidPercentage = $totalReceivables > 0 ? ($paidReceivables / $totalReceivables) * 100 : 0;
        
        // Estatísticas de projetos
        $totalProjects = $contract->projects->count();
        $activeProjects = $contract->projects->where('status', 'active')->count();
        
        return view('company.contracts.show', compact(
            'contract', 
            'company',
            'totalReceivables',
            'paidReceivables',
            'pendingReceivables',
            'partialReceivables',
            'overdueReceivables',
            'paidPercentage',
            'totalProjects',
            'activeProjects'
        ));
    }

    public function edit(Contract $contract)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($contract, $company);
        
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->get();
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->get();
        
        return view('company.contracts.edit', compact('contract', 'company', 'clients', 'employees'));
    }

    public function update(Request $request, Contract $contract)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($contract, $company);
        
        $validated = $request->validate([
            'type' => 'required|in:client_recurring,client_fixed,employee_clt,employee_pj',
            'client_id' => 'required_if:type,client_recurring,client_fixed|nullable|exists:clients,id',
            'employee_id' => 'required_if:type,employee_clt,employee_pj|nullable|exists:employees,id',
            'number' => 'nullable|string|max:100|unique:contracts,number,' . $contract->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'has_down_payment' => 'nullable|boolean',
            'down_payment_value' => 'nullable|numeric|min:0',
            'down_payment_date' => 'nullable|date',
            'installments_count' => 'nullable|integer|min:1',
            'payment_frequency' => 'nullable|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'equal_installments' => 'nullable|boolean',
            'first_installment_date' => 'nullable|date',
            'billing_period' => 'nullable|in:monthly,yearly',
            'recurring_due_date_type' => 'nullable|in:last_business_day,first_business_day,fifth_business_day,fixed_day',
            'recurring_due_date_day' => 'nullable|integer|min:1|max:31|required_if:recurring_due_date_type,fixed_day',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'auto_renew' => 'nullable|boolean',
            'status' => 'nullable|in:active,suspended,cancelled,expired',
            'notes' => 'nullable|string',
        ]);

        $contract->update($validated);
        
        // Regenera contas a receber se for contrato recorrente
        if ($contract->type === 'client_recurring' && $contract->billing_period === 'monthly') {
            $recurringService = new RecurringContractService();
            $recurringService->generateReceivablesForMonth($contract, now()->startOfMonth());
            $recurringService->generateReceivablesForMonth($contract, now()->addMonth()->startOfMonth());
        }

        // Regenera parcelas se for contrato fixo e configuração mudou
        if ($contract->type === 'client_fixed' && $request->has('regenerate_installments')) {
            $service = new ContractInstallmentService();
            $service->generateInstallments($contract);
        }
        
        // Regenera contas a receber se for contrato recorrente e configuração mudou
        if ($contract->type === 'client_recurring' && $contract->billing_period === 'monthly') {
            $recurringService = new RecurringContractService();
            $recurringService->generateReceivablesForMonth($contract, now()->startOfMonth());
            $recurringService->generateReceivablesForMonth($contract, now()->addMonth()->startOfMonth());
        }

        return redirect()->route('company.contracts.index')
            ->with('success', 'Contrato atualizado com sucesso!');
    }

    public function destroy(Contract $contract)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($contract, $company);
        
        $contract->delete();

        return redirect()->route('company.contracts.index')
            ->with('success', 'Contrato removido com sucesso!');
    }

    protected function authorizeAccess(Contract $contract, Company $company): void
    {
        if ($contract->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}

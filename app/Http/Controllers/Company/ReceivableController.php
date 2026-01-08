<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivableController extends Controller
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

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $query = Receivable::where('company_id', $company->id)
            ->with(['client', 'project', 'contract']);
        
        // Filtro de mês (formato: Y-m, ex: 2026-01)
        $monthFilter = $request->input('month', now()->format('Y-m'));
        if ($monthFilter) {
            $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthFilter);
            $query->where(function($q) use ($selectedMonth) {
                $q->where(function($subQ) use ($selectedMonth) {
                    $subQ->where('status', 'paid')
                         ->whereYear('paid_date', $selectedMonth->year)
                         ->whereMonth('paid_date', $selectedMonth->month);
                })->orWhere(function($subQ) use ($selectedMonth) {
                    $subQ->where('status', '!=', 'paid')
                         ->whereYear('due_date', $selectedMonth->year)
                         ->whereMonth('due_date', $selectedMonth->month);
                });
            });
        }
        
        // Filtro de status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro de cliente
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $receivables = $query->latest('due_date')->get();
        
        // Estatísticas
        $stats = [
            'total' => Receivable::where('company_id', $company->id)->count(),
            'pending' => Receivable::where('company_id', $company->id)->where('status', 'pending')->count(),
            'paid' => Receivable::where('company_id', $company->id)->where('status', 'paid')->count(),
            'partial' => Receivable::where('company_id', $company->id)->where('status', 'partial')->count(),
            'overdue' => Receivable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
        ];
        
        $clients = \App\Models\Client::where('company_id', $company->id)->where('status', 'active')->get();
        
        // Detecta se é mobile
        $isMobile = $request->has('mobile') || 
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));
        
        if ($isMobile) {
            return view('company.receivables.index-mobile', compact('receivables', 'company', 'monthFilter', 'stats', 'clients'));
        }
        
        return view('company.receivables.index', compact('receivables', 'company', 'monthFilter', 'stats', 'clients'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->get();
        $projects = Project::where('company_id', $company->id)->get();
        $contracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->get();
        
        return view('company.receivables.create', compact('company', 'clients', 'projects', 'contracts'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'type' => 'required|in:project,recurring,other',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'installment_number' => 'nullable|integer|min:1',
            'total_installments' => 'nullable|integer|min:1',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['status'] = 'pending';

        Receivable::create($validated);

        return redirect()->route('company.receivables.index')
            ->with('success', 'Conta a receber criada com sucesso!');
    }

    public function show(Receivable $receivable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($receivable, $company);
        
        $receivable->load('client', 'project', 'contract');
        return view('company.receivables.show', compact('receivable', 'company'));
    }

    public function edit(Receivable $receivable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($receivable, $company);
        
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->get();
        $projects = Project::where('company_id', $company->id)->get();
        $contracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->get();
        
        return view('company.receivables.edit', compact('receivable', 'company', 'clients', 'projects', 'contracts'));
    }

    public function update(Request $request, Receivable $receivable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($receivable, $company);
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'type' => 'required|in:project,recurring,other',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'nullable|in:pending,paid,partial,overdue,cancelled',
            'paid_date' => 'required_if:status,paid|required_if:status,partial|nullable|date',
            'paid_value' => 'nullable|numeric|min:0',
            'installment_number' => 'nullable|integer|min:1',
            'total_installments' => 'nullable|integer|min:1',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // Se status for paid/partial e não tiver paid_date, define como hoje
        $status = $validated['status'] ?? $receivable->status;
        if (in_array($status, ['paid', 'partial']) && empty($validated['paid_date'])) {
            $validated['paid_date'] = now()->toDateString();
        }
        
        // Se status for paid e não tiver paid_value, define como valor total
        if ($status === 'paid' && empty($validated['paid_value'])) {
            $validated['paid_value'] = $validated['value'];
        }
        
        // Se status for partial e não tiver paid_value, mantém o atual ou define como 0
        if ($status === 'partial' && empty($validated['paid_value'])) {
            $validated['paid_value'] = $receivable->paid_value ?? 0;
        }

        $receivable->update($validated);

        return redirect()->route('company.receivables.index')
            ->with('success', 'Conta a receber atualizada com sucesso!');
    }

    public function destroy(Receivable $receivable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($receivable, $company);
        
        $receivable->delete();

        return redirect()->route('company.receivables.index')
            ->with('success', 'Conta a receber removida com sucesso!');
    }

    public function markAsPaid(Request $request, Receivable $receivable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($receivable, $company);
        
        $isPartial = $request->has('partial_payment') && $request->input('partial_payment') == '1';
        
        $rules = [
            'paid_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'partial_payment' => 'nullable|boolean',
        ];
        
        if ($isPartial) {
            $rules['paid_value'] = 'required|numeric|min:0|max:' . (float) $receivable->value;
        }
        
        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        // Converte valor de string formatada para número
        $paidValue = null;
        if ($isPartial) {
            // Se já tem valor pago, soma com o novo
            $paidValueInput = $validated['paid_value'] ?? 0;
            $newPayment = is_string($paidValueInput) 
                ? str_replace(['.', ','], ['', '.'], $paidValueInput)
                : (float) $paidValueInput;
            $newPayment = (float) $newPayment;
            $alreadyPaid = (float) ($receivable->paid_value ?? 0);
            $paidValue = $alreadyPaid + $newPayment;
            
            // Não pode ultrapassar o valor total
            $totalValue = (float) $receivable->value;
            if ($paidValue > $totalValue) {
                $paidValue = $totalValue;
            }
        } else {
            // Pagamento total
            $paidValue = (float) $receivable->value;
        }

        try {
            $receivable->markAsPaid($validated['paid_date'], $validated['payment_method'] ?? null, $paidValue);

            $message = ($paidValue >= (float) $receivable->value) 
                ? 'Conta a receber marcada como paga!' 
                : 'Pagamento parcial registrado!';

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Erro ao marcar conta como paga: ' . $e->getMessage(), [
                'receivable_id' => $receivable->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao registrar pagamento. Por favor, tente novamente.'])
                ->withInput();
        }
    }

    protected function authorizeAccess(Receivable $receivable, Company $company): void
    {
        if ($receivable->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}

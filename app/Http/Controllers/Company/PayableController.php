<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payable;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayableController extends Controller
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
        
        $query = Payable::where('company_id', $company->id)
            ->with(['employee', 'project']);
        
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
        
        // Filtro de tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $payables = $query->latest('due_date')->get();
        
        // Estatísticas
        $stats = [
            'total' => Payable::where('company_id', $company->id)->count(),
            'pending' => Payable::where('company_id', $company->id)->where('status', 'pending')->count(),
            'paid' => Payable::where('company_id', $company->id)->where('status', 'paid')->count(),
            'overdue' => Payable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
        ];
        
        // Detecta se é mobile
        $isMobile = $request->has('mobile') || 
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));
        
        if ($isMobile) {
            return view('company.payables.index-mobile', compact('payables', 'company', 'monthFilter', 'stats'));
        }
        
        return view('company.payables.index', compact('payables', 'company', 'monthFilter', 'stats'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->get();
        $projects = Project::where('company_id', $company->id)->get();
        
        return view('company.payables.create', compact('company', 'employees', 'projects'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'type' => 'required|in:salary,service,supplier,other',
            'category' => 'nullable|in:clt,pj,supplier,recurring',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'supplier_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['status'] = 'pending';

        Payable::create($validated);

        return redirect()->route('company.payables.index')
            ->with('success', 'Conta a pagar criada com sucesso!');
    }

    public function show(Payable $payable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($payable, $company);
        
        $payable->load('employee', 'project');
        return view('company.payables.show', compact('payable', 'company'));
    }

    public function edit(Payable $payable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($payable, $company);
        
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->get();
        $projects = Project::where('company_id', $company->id)->get();
        
        return view('company.payables.edit', compact('payable', 'company', 'employees', 'projects'));
    }

    public function update(Request $request, Payable $payable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($payable, $company);

        // Normaliza valor monetário (formato brasileiro ou número)
        $request->merge([
            'value' => $this->normalizeMoney($request->input('value'), $payable->value),
        ]);

        try {
            $validated = $request->validate([
                'employee_id' => 'nullable|exists:employees,id',
                'project_id' => 'nullable|exists:projects,id',
                'type' => 'required|in:salary,service,supplier,other',
                'category' => 'nullable|in:clt,pj,supplier,recurring',
                'description' => 'required|string|max:255',
                'value' => 'required|numeric|min:0',
                'due_date' => 'required|date',
                'status' => 'nullable|in:pending,paid,overdue,cancelled',
                'paid_date' => 'required_if:status,paid|nullable|date',
                'payment_method' => 'nullable|string|max:50',
                'supplier_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        // Preserva employee_id e project_id quando não enviados no formulário
        if (! $request->has('employee_id')) {
            $validated['employee_id'] = $payable->employee_id;
        }
        if (! $request->has('project_id')) {
            $validated['project_id'] = $payable->project_id;
        }

        // Se status for paid e não tiver paid_date, define como hoje
        if (($validated['status'] ?? $payable->status) === 'paid' && empty($validated['paid_date'])) {
            $validated['paid_date'] = now()->toDateString();
        }

        $payable->update($validated);

        return redirect()->route('company.payables.index')
            ->with('success', 'Conta a pagar atualizada com sucesso!');
    }

    public function destroy(Payable $payable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($payable, $company);
        
        $payable->delete();

        return redirect()->route('company.payables.index')
            ->with('success', 'Conta a pagar removida com sucesso!');
    }

    public function markAsPaid(Request $request, Payable $payable)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($payable, $company);
        
        $validated = $request->validate([
            'paid_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $payable->markAsPaid($validated['paid_date'], $validated['payment_method'] ?? null);

        return redirect()->back()
            ->with('success', 'Conta a pagar marcada como paga!');
    }

    protected function authorizeAccess(Payable $payable, Company $company): void
    {
        if ($payable->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }

    /**
     * Converte valor monetário em formato brasileiro (1.234,56) para numérico.
     */
    protected function normalizeMoney(mixed $value, mixed $fallback = null): ?float
    {
        if ($value === null || $value === '') {
            return $fallback !== null ? (float) $fallback : null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $str = preg_replace('/\s+/', '', (string) $value);
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
        return $str !== '' ? (float) $str : ($fallback !== null ? (float) $fallback : null);
    }
}

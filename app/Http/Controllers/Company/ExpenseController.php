<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payable;
use App\Models\Supplier;
use App\Services\FixedExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    protected function getCurrentCompany(): Company
    {
        $user = Auth::user();
        
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
        $type = $request->get('type', 'fixed'); // fixed ou variable
        
        $expenses = Expense::where('company_id', $company->id)
            ->where('type', $type)
            ->with(['category', 'supplier'])
            ->latest()
            ->paginate(15);
        
        $fixedCount = Expense::where('company_id', $company->id)->where('type', 'fixed')->count();
        $variableCount = Expense::where('company_id', $company->id)->where('type', 'variable')->count();
        
        // Detecta se é mobile
        $isMobile = $request->has('mobile') || 
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));
        
        if ($isMobile) {
            return view('company.expenses.index-mobile', compact('expenses', 'company', 'type', 'fixedCount', 'variableCount'));
        }
        
        return view('company.expenses.index', compact('expenses', 'company', 'type', 'fixedCount', 'variableCount'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        $categories = ExpenseCategory::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        return view('company.expenses.create', compact('company', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'type' => 'required|in:fixed,variable',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'due_date_day' => 'required_if:type,fixed|nullable|integer|min:1|max:31',
            'due_date' => 'required_if:type,variable|nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_active'] = true;

        $expense = Expense::create($validated);
        
        // Se for despesa variável, cria uma conta a pagar
        if ($expense->type === 'variable') {
            $supplierName = $expense->supplier ? $expense->supplier->name : null;
            Payable::create([
                'company_id' => $company->id,
                'type' => 'service',
                'category' => 'other',
                'description' => $expense->description,
                'value' => $expense->value,
                'due_date' => $expense->due_date,
                'status' => 'pending',
                'supplier_name' => $supplierName,
                'notes' => $expense->notes,
            ]);
        } else {
            // Se for despesa fixa, gera as duplicatas
            $service = new FixedExpenseService();
            $service->generatePayablesForFixedExpenses();
        }

        return redirect()->route('company.expenses.index', ['type' => $expense->type])
            ->with('success', 'Despesa criada com sucesso!');
    }

    public function show(Expense $expense)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        return view('company.expenses.show', compact('expense', 'company'));
    }

    public function edit(Expense $expense)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        $categories = ExpenseCategory::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        
        return view('company.expenses.edit', compact('expense', 'company', 'categories', 'suppliers'));
    }

    public function update(Request $request, Expense $expense)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        $validated = $request->validate([
            'type' => 'required|in:fixed,variable',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'due_date_day' => 'required_if:type,fixed|nullable|integer|min:1|max:31',
            'due_date' => 'required_if:type,variable|nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $expense->update($validated);
        
        // Se for despesa fixa, atualiza as duplicatas pendentes
        if ($expense->type === 'fixed') {
            $service = new FixedExpenseService();
            $service->updatePendingPayablesForExpense($expense);
        }

        return redirect()->route('company.expenses.index', ['type' => $expense->type])
            ->with('success', 'Despesa atualizada com sucesso!');
    }

    public function destroy(Expense $expense)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        $type = $expense->type;
        $expense->delete();

        return redirect()->route('company.expenses.index', ['type' => $type])
            ->with('success', 'Despesa removida com sucesso!');
    }

    protected function authorizeAccess(Expense $expense, Company $company): void
    {
        if ($expense->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}

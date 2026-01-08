<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
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
        $categories = ExpenseCategory::where('company_id', $company->id)
            ->latest()
            ->paginate(15);
        
        // Detecta se é mobile
        $isMobile = $request->has('mobile') || 
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));
        
        if ($isMobile) {
            return view('company.expense-categories.index-mobile', compact('categories', 'company'));
        }
        
        return view('company.expense-categories.index', compact('categories', 'company'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        return view('company.expense-categories.create', compact('company'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        ExpenseCategory::create($validated);

        return redirect()->route('company.expense-categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expenseCategory, $company);
        
        return view('company.expense-categories.show', compact('expenseCategory', 'company'));
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expenseCategory, $company);
        
        return view('company.expense-categories.edit', compact('expenseCategory', 'company'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expenseCategory, $company);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'nullable|boolean',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('company.expense-categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expenseCategory, $company);
        
        $expenseCategory->delete();

        return redirect()->route('company.expense-categories.index')
            ->with('success', 'Categoria removida com sucesso!');
    }

    protected function authorizeAccess(ExpenseCategory $expenseCategory, Company $company): void
    {
        if ($expenseCategory->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}

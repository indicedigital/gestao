<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
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

    public function index()
    {
        $company = $this->getCurrentCompany();
        $suppliers = Supplier::where('company_id', $company->id)
            ->latest()
            ->paginate(15);
        
        return view('company.suppliers.index', compact('suppliers', 'company'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        return view('company.suppliers.create', compact('company'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:cpf,cnpj',
            'document' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Supplier::create($validated);

        return redirect()->route('company.suppliers.index')
            ->with('success', 'Fornecedor criado com sucesso!');
    }

    public function show(Supplier $supplier)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($supplier, $company);
        
        return view('company.suppliers.show', compact('supplier', 'company'));
    }

    public function edit(Supplier $supplier)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($supplier, $company);
        
        return view('company.suppliers.edit', compact('supplier', 'company'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($supplier, $company);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:cpf,cnpj',
            'document' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('company.suppliers.index')
            ->with('success', 'Fornecedor atualizado com sucesso!');
    }

    public function destroy(Supplier $supplier)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($supplier, $company);
        
        $supplier->delete();

        return redirect()->route('company.suppliers.index')
            ->with('success', 'Fornecedor removido com sucesso!');
    }

    protected function authorizeAccess(Supplier $supplier, Company $company): void
    {
        if ($supplier->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}

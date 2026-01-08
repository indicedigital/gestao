<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Lista todas as empresas
     */
    public function index()
    {
        $companies = Company::latest()->paginate(15);
        
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Mostra o formulário de criação
     */
    public function create()
    {
        $users = User::where('is_super_admin', false)->get();
        return view('admin.companies.create', compact('users'));
    }

    /**
     * Armazena uma nova empresa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,suspended,cancelled',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        $company = Company::create($validated);

        // Se tiver owner, vincula como admin
        if (isset($validated['owner_id'])) {
            $company->users()->attach($validated['owner_id'], [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]);
        }

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Mostra uma empresa específica
     */
    public function show(Company $company)
    {
        $company->load('owner', 'users');
        return view('admin.companies.show', compact('company'));
    }

    /**
     * Mostra o formulário de edição
     */
    public function edit(Company $company)
    {
        $users = User::where('is_super_admin', false)->get();
        return view('admin.companies.edit', compact('company', 'users'));
    }

    /**
     * Atualiza uma empresa
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,suspended,cancelled',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $company->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Atualiza owner se mudou
        if (isset($validated['owner_id']) && $validated['owner_id'] != $company->owner_id) {
            // Remove role owner do antigo
            if ($company->owner_id) {
                $company->users()->updateExistingPivot($company->owner_id, ['role' => 'admin']);
            }
            // Adiciona novo owner
            if (!$company->users()->where('user_id', $validated['owner_id'])->exists()) {
                $company->users()->attach($validated['owner_id'], [
                    'role' => 'owner',
                    'is_active' => true,
                    'joined_at' => now(),
                ]);
            } else {
                $company->users()->updateExistingPivot($validated['owner_id'], ['role' => 'owner']);
            }
        }

        $company->update($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove uma empresa
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa removida com sucesso!');
    }
}

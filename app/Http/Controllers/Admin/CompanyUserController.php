<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class CompanyUserController extends Controller
{
    /**
     * Lista usuários da empresa
     */
    public function index(Company $company)
    {
        $company->load('users');
        return view('admin.companies.users.index', compact('company'));
    }

    /**
     * Mostra formulário para criar usuário na empresa
     */
    public function create(Company $company)
    {
        return view('admin.companies.users.create', compact('company'));
    }

    /**
     * Cria novo usuário vinculado à empresa
     */
    public function store(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,manager,user',
        ]);

        // Cria o usuário
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];
        
        // Adiciona status apenas se a coluna existir
        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = 'active';
        }
        
        $user = User::create($userData);

        // Vincula à empresa
        $company->users()->attach($user->id, [
            'role' => $validated['role'],
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return redirect()->route('admin.companies.users.index', $company)
            ->with('success', 'Usuário criado e vinculado à empresa com sucesso!');
    }

    /**
     * Vincula usuário existente à empresa
     */
    public function attach(Request $request, Company $company)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,manager,user',
        ]);

        // Verifica se já está vinculado
        if ($company->users()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'Este usuário já está vinculado à empresa.');
        }

        // Vincula à empresa
        $company->users()->attach($validated['user_id'], [
            'role' => $validated['role'],
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return redirect()->route('admin.companies.users.index', $company)
            ->with('success', 'Usuário vinculado à empresa com sucesso!');
    }

    /**
     * Atualiza role do usuário na empresa
     */
    public function updateRole(Request $request, Company $company, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:owner,admin,manager,user',
        ]);

        $company->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Papel do usuário atualizado com sucesso!');
    }

    /**
     * Remove usuário da empresa
     */
    public function detach(Company $company, User $user)
    {
        // Não permite remover o owner
        $pivot = $company->users()->where('user_id', $user->id)->first();
        if ($pivot && $pivot->pivot->role === 'owner') {
            return back()->with('error', 'Não é possível remover o proprietário da empresa.');
        }

        $company->users()->detach($user->id);

        return back()->with('success', 'Usuário removido da empresa com sucesso!');
    }
}

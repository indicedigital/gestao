<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Lista todos os usuários
     */
    public function index()
    {
        $users = User::latest()->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Mostra o formulário de criação
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Armazena um novo usuário
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'nullable|in:active,pending,blocked',
            'is_super_admin' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Remove campos que não existem na tabela
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
        
        // Adiciona status apenas se a coluna existir
        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = $validated['status'] ?? 'active';
        }
        
        // Adiciona is_super_admin apenas se a coluna existir
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $userData['is_super_admin'] = $validated['is_super_admin'] ?? false;
        }

        User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Mostra um usuário específico
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Mostra o formulário de edição
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Atualiza um usuário
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'nullable|in:active,pending,blocked',
            'is_super_admin' => 'nullable|boolean',
        ]);

        // Prepara dados para atualização
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];
        
        if (isset($validated['password']) && $validated['password']) {
            $updateData['password'] = Hash::make($validated['password']);
        }
        
        // Adiciona status apenas se a coluna existir
        if (Schema::hasColumn('users', 'status') && isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }
        
        // Adiciona is_super_admin apenas se a coluna existir
        if (Schema::hasColumn('users', 'is_super_admin') && isset($validated['is_super_admin'])) {
            $updateData['is_super_admin'] = $validated['is_super_admin'];
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove um usuário
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso!');
    }
}

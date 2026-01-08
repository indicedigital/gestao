<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Mostra o formulário de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Processa o registro
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'company_name' => 'required|string|max:255',
            'terms' => 'required|accepted',
        ]);

        // Cria o usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Login automático
        Auth::login($user);

        // Verifica se tem empresa vinculada e redireciona
        $company = $user->currentCompany();
        if ($company) {
            session(['current_company_id' => $company->id]);
            return redirect()->route('company.dashboard')
                ->with('success', 'Conta e empresa criadas com sucesso!');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Conta criada com sucesso!');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Mostra o formulário de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processa o login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        $user = Auth::user();

        // Verifica se é super admin
        if ($user->is_super_admin ?? false) {
            // Limpa qualquer contexto de empresa da sessão
            session()->forget('current_company_id');
            return redirect()->route('admin.dashboard');
        }

        // Verifica se tem empresa vinculada
        $company = $user->currentCompany();
        if ($company) {
            session(['current_company_id' => $company->id]);

            if ($user->isClientUser($company->id)) {
                return redirect()->route('portal.dashboard');
            }

            $authz = app(\App\Services\CompanyAuthorizationService::class);
            $firstRoute = $authz->firstAccessibleRouteName();
            if ($firstRoute) {
                return redirect()->route($firstRoute);
            }

            return redirect()->route('company.projects.index');
        }

        // Se não tem empresa, redireciona para dashboard genérico (pode mostrar mensagem)
        return redirect()->route('dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Limpa o contexto da empresa no logout
        session()->forget('current_company_id');

        return redirect()->route('login');
    }
}

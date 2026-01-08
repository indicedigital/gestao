<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard administrativo
     */
    public function index()
    {
        $user = Auth::user();
        
        // Verifica se é super admin
        if (!($user->is_super_admin ?? false)) {
            abort(403, 'Acesso negado. Apenas super administradores podem acessar esta área.');
        }
        
        // Limpa qualquer contexto de empresa da sessão
        session()->forget('current_company_id');
        
        return view('admin.dashboard', [
            'user' => $user,
        ]);
    }
}

<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanyUserController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\ClientController;
use App\Http\Controllers\Company\ProjectController;
use App\Http\Controllers\Company\ContractController;
use App\Http\Controllers\Company\ReceivableController;
use App\Http\Controllers\Company\PayableController;
use App\Http\Controllers\Company\EmployeeController;
use App\Http\Controllers\Company\ExpenseController;
use App\Http\Controllers\Company\ExpenseCategoryController;
use App\Http\Controllers\Company\NotificationController;
use App\Http\Controllers\Company\SupplierController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Rota inicial - redireciona para login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação (apenas para visitantes)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Registro
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Recuperação de senha
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Rotas de verificação de e-mail (requer autenticação)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Logout (requer autenticação)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Rotas protegidas - Dashboard principal (redireciona baseado no tipo de usuário)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        // Se for super admin, redireciona para admin
        if ($user->is_super_admin ?? false) {
            return redirect()->route('admin.dashboard');
        }
        
        // Se tiver empresa, redireciona para dashboard da empresa
        $company = $user->currentCompany();
        if ($company) {
            session(['current_company_id' => $company->id]);
            return redirect()->route('company.dashboard');
        }
        
        // Caso contrário, mostra mensagem
        return view('dashboard')->with('message', 'Você não possui uma empresa vinculada. Entre em contato com o administrador.');
    })->name('dashboard');
});

// Rotas da Empresa (requer autenticação, verificação e não ser super admin)
Route::middleware(['auth', 'verified', \App\Http\Middleware\GenerateRecurringReceivables::class, \App\Http\Middleware\GenerateFixedExpenses::class])->prefix('company')->name('company.')->group(function () {
    // Notificações
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Clientes
    Route::get('clients/export/excel', [ClientController::class, 'exportExcel'])->name('clients.export.excel');
    Route::get('clients/export/pdf', [ClientController::class, 'exportPdf'])->name('clients.export.pdf');
    Route::resource('clients', ClientController::class);
    
    // Projetos
    Route::resource('projects', ProjectController::class);
    
    // Contratos
    Route::resource('contracts', ContractController::class);
    
    // Contas a Receber
    Route::resource('receivables', ReceivableController::class);
    Route::post('receivables/{receivable}/mark-as-paid', [ReceivableController::class, 'markAsPaid'])->name('receivables.mark-as-paid');
    
    // Contas a Pagar
    Route::resource('payables', PayableController::class);
    Route::post('payables/{payable}/mark-as-paid', [PayableController::class, 'markAsPaid'])->name('payables.mark-as-paid');
    
           // Funcionários
           Route::resource('employees', EmployeeController::class);
           Route::post('employees/generate-payroll', [EmployeeController::class, 'generatePayroll'])->name('employees.generate-payroll');
           
           // Despesas
           Route::resource('expenses', ExpenseController::class);
           
           // Fornecedores
           Route::resource('suppliers', SupplierController::class);
           
           // Categorias de Despesas (Configurações)
           Route::resource('expense-categories', ExpenseCategoryController::class);
});

// Rotas de Admin (requer autenticação e verificação de e-mail)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gerenciamento de Usuários
    Route::resource('users', UserController::class);
    
    // Gerenciamento de Empresas
    Route::resource('companies', CompanyController::class);
    
    // Usuários da Empresa
    Route::prefix('companies/{company}')->name('companies.')->group(function () {
        Route::get('/users', [CompanyUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [CompanyUserController::class, 'create'])->name('users.create');
        Route::post('/users', [CompanyUserController::class, 'store'])->name('users.store');
        Route::post('/users/attach', [CompanyUserController::class, 'attach'])->name('users.attach');
        Route::put('/users/{user}/role', [CompanyUserController::class, 'updateRole'])->name('users.update-role');
        Route::delete('/users/{user}', [CompanyUserController::class, 'detach'])->name('users.detach');
    });
});

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
use App\Http\Controllers\Company\FiscalEntryNoteController;
use App\Http\Controllers\Company\FiscalExitNoteController;
use App\Http\Controllers\Company\AccountingReportController;
use App\Http\Controllers\Company\AiAssistantController;
use App\Http\Controllers\Company\ProjectKanbanController;
use App\Http\Controllers\Company\TaskController;
use App\Http\Controllers\Company\SubtaskController;
use App\Http\Controllers\Company\DailyController;
use App\Http\Controllers\Company\ProductivityController;
use App\Http\Controllers\Company\DeveloperDashboardController;
use App\Http\Controllers\Company\LeadController;
use App\Http\Controllers\Company\MemberAccessController;
use App\Http\Controllers\Company\PermissionProfileController;
use App\Http\Controllers\Company\TutorialController;
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
            if ($user->isClientUser($company->id)) {
                return redirect()->route('portal.dashboard');
            }

            $authz = app(\App\Services\CompanyAuthorizationService::class);
            $firstRoute = $authz->firstAccessibleRouteName();
            if ($firstRoute) {
                return redirect()->route($firstRoute);
            }

            return redirect()->route('company.dashboard');
        }
        
        // Caso contrário, mostra mensagem
        return view('dashboard')->with('message', 'Você não possui uma empresa vinculada. Entre em contato com o administrador.');
    })->name('dashboard');
});

// Rotas da Empresa (requer autenticação, verificação e não ser super admin)
Route::middleware(['auth', 'verified', 'company.member', 'not.client', 'module.access'])->prefix('company')->name('company.')->group(function () {
    // Notificações
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/developer-dashboard', [DeveloperDashboardController::class, 'index'])->name('developer-dashboard');
    Route::post('/dashboard/update-cash', [DashboardController::class, 'updateCash'])->name('dashboard.update-cash');
    Route::get('/dashboard/cash-report-data', [DashboardController::class, 'cashReportData'])->name('dashboard.cash-report-data');
    Route::post('/ai-assistant/chat', [AiAssistantController::class, 'chat'])->name('ai-assistant.chat');

    Route::get('/tutorial', [TutorialController::class, 'index'])->name('tutorial');
    
    // Clientes
    Route::get('clients/export/excel', [ClientController::class, 'exportExcel'])->name('clients.export.excel');
    Route::get('clients/export/pdf', [ClientController::class, 'exportPdf'])->name('clients.export.pdf');
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/access', [MemberAccessController::class, 'storeClient'])->name('clients.access.store');
    Route::put('clients/{client}/access', [MemberAccessController::class, 'updateClient'])->name('clients.access.update');
    Route::delete('clients/{client}/access', [MemberAccessController::class, 'destroyClient'])->name('clients.access.destroy');
    
    // Projetos
    Route::resource('projects', ProjectController::class);
    Route::get('projects/{project}/kanban', [ProjectKanbanController::class, 'show'])->name('projects.kanban');
    Route::get('projects/{project}/dashboard', [ProjectKanbanController::class, 'dashboard'])->name('projects.dashboard');
    Route::get('projects/{project}/team', [ProjectKanbanController::class, 'team'])->name('projects.team');
    Route::put('projects/{project}/team', [ProjectKanbanController::class, 'updateTeam'])->name('projects.team.update');

    // Tasks
    Route::get('tasks/export/excel', [TaskController::class, 'exportExcel'])->name('tasks.export.excel');
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::get('tasks/{task}/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');
    Route::post('tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('tasks.subtasks.store');
    Route::put('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('tasks.subtasks.update');
    Route::delete('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('tasks.subtasks.destroy');

    // Dailies
    Route::get('dailies/productivity', [ProductivityController::class, 'index'])->name('dailies.productivity');
    Route::get('dailies/productivity/tab', [ProductivityController::class, 'tab'])->name('dailies.productivity.tab');
    Route::get('dailies/export/excel', [DailyController::class, 'exportExcel'])->name('dailies.export.excel');
    Route::resource('dailies', DailyController::class)->only(['index', 'store', 'destroy']);

    // Leads
    Route::resource('leads', LeadController::class)->except(['show']);
    
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
           Route::post('employees/{employee}/access', [MemberAccessController::class, 'storeEmployee'])->name('employees.access.store');
           Route::put('employees/{employee}/access', [MemberAccessController::class, 'updateEmployee'])->name('employees.access.update');
           Route::delete('employees/{employee}/access', [MemberAccessController::class, 'destroyEmployee'])->name('employees.access.destroy');
           Route::post('employees/generate-payroll', [EmployeeController::class, 'generatePayroll'])->name('employees.generate-payroll');
           
           // Despesas (rota específica antes do resource para não conflitar com {expense})
           Route::get('expenses/monthly-evolution', [ExpenseController::class, 'monthlyEvolution'])->name('expenses.monthly-evolution');
           Route::resource('expenses', ExpenseController::class);
           
           // Fornecedores
           Route::resource('suppliers', SupplierController::class);
           
           // Categorias de Despesas (Configurações)
           Route::resource('expense-categories', ExpenseCategoryController::class);

           // Perfis de permissão (admin empresa)
           Route::get('permission-profiles', [PermissionProfileController::class, 'index'])->name('permission-profiles.index');
           Route::get('permission-profiles/create', [PermissionProfileController::class, 'create'])->name('permission-profiles.create');
           Route::post('permission-profiles', [PermissionProfileController::class, 'store'])->name('permission-profiles.store');
           Route::get('permission-profiles/{permissionProfile}/edit', [PermissionProfileController::class, 'edit'])->name('permission-profiles.edit');
           Route::put('permission-profiles/{permissionProfile}', [PermissionProfileController::class, 'update'])->name('permission-profiles.update');
           Route::delete('permission-profiles/{permissionProfile}', [PermissionProfileController::class, 'destroy'])->name('permission-profiles.destroy');
           Route::post('permission-profiles/assign', [PermissionProfileController::class, 'assignMember'])->name('permission-profiles.assign');

           // Contabilidade
           Route::prefix('accounting')->name('accounting.')->group(function () {
               Route::get('report', [AccountingReportController::class, 'monthly'])->name('report');
               Route::get('fiscal-entry-notes/report', [FiscalEntryNoteController::class, 'monthlyReport'])->name('fiscal-entry-notes.report');
               Route::post('fiscal-entry-notes/{fiscal_entry_note}/toggle-issued', [FiscalEntryNoteController::class, 'toggleIssued'])->name('fiscal-entry-notes.toggle-issued');
               Route::resource('fiscal-entry-notes', FiscalEntryNoteController::class)->except(['show']);
               Route::post('fiscal-exit-notes/sync-from-receivables', [FiscalExitNoteController::class, 'syncFromReceivables'])->name('fiscal-exit-notes.sync-from-receivables');
               Route::post('fiscal-exit-notes/{fiscal_exit_note}/mark-issued', [FiscalExitNoteController::class, 'markIssued'])->name('fiscal-exit-notes.mark-issued');
               Route::post('fiscal-exit-notes/{fiscal_exit_note}/toggle-issued', [FiscalExitNoteController::class, 'toggleIssued'])->name('fiscal-exit-notes.toggle-issued');
               Route::resource('fiscal-exit-notes', FiscalExitNoteController::class)->only(['index', 'edit', 'update', 'destroy']);
           });
});

// Portal do Cliente
Route::middleware(['auth', 'verified', 'company.member', 'client.role'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/tutorial', [TutorialController::class, 'index'])->name('tutorial');
    Route::get('/', [\App\Http\Controllers\Portal\ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/projects/{project}/kanban', [\App\Http\Controllers\Portal\ClientPortalController::class, 'kanban'])->name('kanban');
    Route::get('/tasks/create', [\App\Http\Controllers\Portal\ClientPortalController::class, 'createTask'])->name('tasks.create');
    Route::post('/tasks', [\App\Http\Controllers\Portal\ClientPortalController::class, 'storeTask'])->name('tasks.store');
    Route::get('/tasks/{task}', [\App\Http\Controllers\Portal\ClientPortalController::class, 'showTask'])->name('tasks.show');
    Route::post('/tasks/{task}/comments', [\App\Http\Controllers\Portal\ClientPortalController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [\App\Http\Controllers\Portal\ClientPortalController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::get('/tasks/{task}/attachments/{attachment}/download', [\App\Http\Controllers\Portal\ClientPortalController::class, 'downloadAttachment'])->name('tasks.attachments.download');
    Route::post('/tasks/{task}/approve', [\App\Http\Controllers\Portal\ClientPortalController::class, 'approveHomologation'])->name('tasks.approve');
});

// Rotas de Admin (requer autenticação e verificação de e-mail)
Route::middleware(['auth', 'verified', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
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

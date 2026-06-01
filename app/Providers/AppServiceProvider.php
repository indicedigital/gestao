<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\CompanyPermissionProfile;
use App\Models\Contract;
use App\Models\Daily;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Payable;
use App\Models\Project;
use App\Models\Receivable;
use App\Models\Supplier;
use App\Models\Task;
use App\Services\CompanyAuthorizationService;
use App\Support\CurrentCompanyContext;
use App\Support\PermissionModules;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CompanyAuthorizationService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();
            if ($user->is_super_admin ?? false) {
                return;
            }

            $companyId = session('current_company_id');
            if (! $companyId) {
                return;
            }

            $view->with('authz', app(CompanyAuthorizationService::class));

            $membership = CurrentCompanyContext::membership();
            if ($membership) {
                $view->with('currentCompany', $membership);
            }

            $authz = app(CompanyAuthorizationService::class);
            $modules = $authz->moduleAccessMap();

            $view->with('sidebarAccess', [
                'company_dashboard' => $authz->canViewCompanyDashboard(),
                'developer_dashboard' => $authz->canViewDeveloperDashboard(),
                'productivity' => $authz->canViewProductivity(),
                'modules' => $modules,
                'manage_profiles' => $authz->canManageProfiles(),
            ]);
        });

        Route::bind('project', function ($value) {
            $companyId = session('current_company_id');
            $query = Project::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return $query->whereKey($value)->firstOrFail();
        });

        Route::bind('task', function ($value) {
            $companyId = session('current_company_id');
            $query = Task::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return $query->whereKey($value)->firstOrFail();
        });

        Route::bind('daily', function ($value) {
            $companyId = session('current_company_id');
            $query = Daily::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return $query->whereKey($value)->firstOrFail();
        });

        $this->bindTenantScoped('client', Client::class);
        $this->bindTenantScoped('employee', Employee::class);
        $this->bindTenantScoped('contract', Contract::class);
        $this->bindTenantScoped('receivable', Receivable::class);
        $this->bindTenantScoped('payable', Payable::class);
        $this->bindTenantScoped('lead', Lead::class);
        $this->bindTenantScoped('expense', Expense::class);
        $this->bindTenantScoped('supplier', Supplier::class);
        $this->bindTenantScoped('permissionProfile', CompanyPermissionProfile::class);
    }

    protected function bindTenantScoped(string $parameter, string $modelClass): void
    {
        Route::bind($parameter, function ($value) use ($modelClass) {
            $companyId = session('current_company_id');
            $query = $modelClass::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return $query->whereKey($value)->firstOrFail();
        });
    }
}

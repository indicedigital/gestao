<?php

namespace App\Support;

class PermissionRouteMapper
{
    /** @var array<string, string> */
    protected static array $prefixMap = [
        'company.dashboard' => 'dashboard',
        'company.developer-dashboard' => 'developer_dashboard',
        'company.ai-assistant' => 'dashboard',
        'company.clients' => 'clients',
        'company.projects.dashboard' => 'project_dashboard',
        'company.projects' => 'projects',
        'company.tasks' => 'tasks',
        'company.dailies.productivity' => 'productivity',
        'company.dailies' => 'dailies',
        'company.leads' => 'leads',
        'company.contracts' => 'contracts',
        'company.receivables' => 'receivables',
        'company.payables' => 'payables',
        'company.employees' => 'employees',
        'company.expenses' => 'expenses',
        'company.suppliers' => 'suppliers',
        'company.expense-categories' => 'expense_categories',
        'company.accounting.fiscal-entry-notes' => 'accounting_entry',
        'company.accounting.fiscal-exit-notes' => 'accounting_exit',
        'company.accounting.report' => 'accounting_report',
        'company.permission-profiles' => 'permission_profiles',
    ];

    public static function moduleForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        if (isset(self::$prefixMap[$routeName])) {
            return self::$prefixMap[$routeName];
        }

        foreach (self::$prefixMap as $prefix => $module) {
            if (str_starts_with($routeName, $prefix.'.') || str_starts_with($routeName, $prefix)) {
                return $module;
            }
        }

        return null;
    }
}

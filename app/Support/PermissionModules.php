<?php

namespace App\Support;

class PermissionModules
{
    public const SCOPE_ALL = 'all';

    public const SCOPE_ASSIGNED = 'assigned';

    public const SCOPE_OWN = 'own';

    /** @return array<string, array{label: string, group: string, scoped: bool, route?: string}> */
    public static function definitions(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'group' => 'Overview',
                'scoped' => false,
                'route' => 'company.dashboard',
            ],
            'developer_dashboard' => [
                'label' => 'Meu Dashboard',
                'group' => 'Overview',
                'scoped' => false,
                'route' => 'company.developer-dashboard',
            ],
            'projects' => [
                'label' => 'Projetos',
                'group' => 'Gestão',
                'scoped' => true,
                'route' => 'company.projects.index',
            ],
            'project_overview' => [
                'label' => 'Projeto — Visão geral',
                'group' => 'Gestão',
                'scoped' => false,
            ],
            'project_financial' => [
                'label' => 'Projeto — Financeiro',
                'group' => 'Gestão',
                'scoped' => false,
            ],
            'project_dashboard' => [
                'label' => 'Projeto — Dashboard',
                'group' => 'Gestão',
                'scoped' => false,
            ],
            'tasks' => [
                'label' => 'Tasks',
                'group' => 'Gestão',
                'scoped' => true,
                'route' => 'company.tasks.index',
            ],
            'dailies' => [
                'label' => 'Daily',
                'group' => 'Gestão',
                'scoped' => true,
                'route' => 'company.dailies.index',
            ],
            'productivity' => [
                'label' => 'Produtividade',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.dailies.productivity',
            ],
            'clients' => [
                'label' => 'Clientes',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.clients.index',
            ],
            'leads' => [
                'label' => 'Leads',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.leads.index',
            ],
            'contracts' => [
                'label' => 'Contratos',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.contracts.index',
            ],
            'employees' => [
                'label' => 'Funcionários',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.employees.index',
            ],
            'expenses' => [
                'label' => 'Despesas',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.expenses.index',
            ],
            'suppliers' => [
                'label' => 'Fornecedores',
                'group' => 'Gestão',
                'scoped' => false,
                'route' => 'company.suppliers.index',
            ],
            'receivables' => [
                'label' => 'Contas a Receber',
                'group' => 'Financeiro',
                'scoped' => false,
                'route' => 'company.receivables.index',
            ],
            'payables' => [
                'label' => 'Contas a Pagar',
                'group' => 'Financeiro',
                'scoped' => false,
                'route' => 'company.payables.index',
            ],
            'expense_categories' => [
                'label' => 'Categorias de Despesas',
                'group' => 'Configurações',
                'scoped' => false,
                'route' => 'company.expense-categories.index',
            ],
            'accounting_entry' => [
                'label' => 'NF de entrada',
                'group' => 'Contabilidade',
                'scoped' => false,
                'route' => 'company.accounting.fiscal-entry-notes.index',
            ],
            'accounting_exit' => [
                'label' => 'NF de saída',
                'group' => 'Contabilidade',
                'scoped' => false,
                'route' => 'company.accounting.fiscal-exit-notes.index',
            ],
            'accounting_report' => [
                'label' => 'Relatório fiscal',
                'group' => 'Contabilidade',
                'scoped' => false,
                'route' => 'company.accounting.report',
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    /** @return array<string, list<string>> */
    public static function grouped(): array
    {
        $groups = [];
        foreach (self::definitions() as $key => $def) {
            $groups[$def['group']][] = $key;
        }

        return $groups;
    }

    public static function scopedModules(): array
    {
        return array_keys(array_filter(
            self::definitions(),
            fn (array $def) => $def['scoped']
        ));
    }

    /** Perfil colaborador padrão (sem dashboard, sem despesas). */
    public static function collaboratorDefaults(): array
    {
        $modules = array_fill_keys(self::keys(), false);
        foreach (['developer_dashboard', 'projects', 'tasks', 'dailies'] as $key) {
            $modules[$key] = true;
        }

        return [
            'modules' => $modules,
            'scopes' => [
                'projects' => self::SCOPE_ASSIGNED,
                'tasks' => self::SCOPE_ASSIGNED,
                'dailies' => self::SCOPE_OWN,
            ],
        ];
    }

    /** Perfil gestor padrão (manager sem perfil customizado). */
    public static function managerDefaults(): array
    {
        $modules = array_fill_keys(self::keys(), true);

        return [
            'modules' => $modules,
            'scopes' => array_fill_keys(self::scopedModules(), self::SCOPE_ALL),
        ];
    }

    /** Perfil diretor — acesso total. */
    public static function directorTemplate(): array
    {
        return self::managerDefaults();
    }

    /** Perfil programador — só gestão operacional, escopo próprio. */
    public static function developerTemplate(): array
    {
        return self::collaboratorDefaults();
    }

    public static function normalizePermissions(?array $permissions): array
    {
        $defaults = self::collaboratorDefaults();
        $modules = array_fill_keys(self::keys(), false);
        $scopes = $defaults['scopes'];

        if (is_array($permissions['modules'] ?? null)) {
            foreach (self::keys() as $key) {
                $modules[$key] = (bool) ($permissions['modules'][$key] ?? false);
            }
        }

        if (is_array($permissions['scopes'] ?? null)) {
            foreach (self::scopedModules() as $key) {
                $scope = $permissions['scopes'][$key] ?? self::SCOPE_ASSIGNED;
                $scopes[$key] = in_array($scope, [self::SCOPE_ALL, self::SCOPE_ASSIGNED, self::SCOPE_OWN], true)
                    ? $scope
                    : self::SCOPE_ASSIGNED;
            }
        }

        return ['modules' => $modules, 'scopes' => $scopes];
    }
}

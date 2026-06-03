<?php

namespace App\Support;

use App\Services\CompanyAuthorizationService;
use Illuminate\Support\Facades\Auth;

class MobileBottomNav
{
    public const MAX_ITEMS = 5;

    /**
     * @return list<array{route: string, params: array, label: string, icon: string, patterns: list<string>, mobile: bool}>
     */
    public static function items(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        if ($user->isClientUser()) {
            return self::clientItems();
        }

        $authz = app(CompanyAuthorizationService::class);
        $modules = $authz->moduleAccessMap();
        $items = [];

        if ($authz->canViewCompanyDashboard()) {
            $items[] = self::item(
                'company.dashboard',
                'Principal',
                'fa-home',
                ['company.dashboard'],
                true,
            );
        } elseif ($authz->canViewDeveloperDashboard()) {
            $items[] = self::item(
                'company.developer-dashboard',
                'Início',
                'fa-code',
                ['company.developer-dashboard'],
                false,
            );
        }

        foreach (self::moduleNavDefinitions() as $moduleKey => $def) {
            if (count($items) >= self::MAX_ITEMS) {
                break;
            }

            if (! ($modules[$moduleKey] ?? false)) {
                continue;
            }

            $items[] = self::item(
                $def['route'],
                $def['label'],
                $def['icon'],
                $def['patterns'],
                $def['mobile'] ?? true,
            );
        }

        if (count($items) < self::MAX_ITEMS && $authz->canViewProductivity()) {
            $items[] = self::item(
                'company.dailies.productivity',
                'Produtividade',
                'fa-chart-line',
                ['company.dailies.productivity'],
                false,
            );
        }

        if ($items === []) {
            $fallback = $authz->firstAccessibleRouteName();
            if ($fallback) {
                $items[] = self::item(
                    $fallback,
                    'Início',
                    'fa-home',
                    [$fallback],
                    str_starts_with($fallback, 'company.'),
                );
            }
        }

        return array_slice($items, 0, self::MAX_ITEMS);
    }

    /**
     * @return list<array{route: string, params: array, label: string, icon: string, patterns: list<string>, mobile: bool}>
     */
    protected static function clientItems(): array
    {
        return [
            self::item('portal.dashboard', 'Início', 'fa-home', [
                'portal.dashboard',
                'portal.kanban',
                'portal.tasks.show',
            ], false),
            self::item('portal.tasks.create', 'Nova', 'fa-plus-circle', ['portal.tasks.create'], false),
            self::item('portal.tutorial', 'Ajuda', 'fa-graduation-cap', ['portal.tutorial'], false),
        ];
    }

    /**
     * @return array<string, array{route: string, label: string, icon: string, patterns: list<string>, mobile?: bool}>
     */
    protected static function moduleNavDefinitions(): array
    {
        return [
            'projects' => [
                'route' => 'company.projects.index',
                'label' => 'Projetos',
                'icon' => 'fa-project-diagram',
                'patterns' => ['company.projects.*'],
            ],
            'tasks' => [
                'route' => 'company.tasks.index',
                'label' => 'Tasks',
                'icon' => 'fa-tasks',
                'patterns' => ['company.tasks.*'],
            ],
            'dailies' => [
                'route' => 'company.dailies.index',
                'label' => 'Daily',
                'icon' => 'fa-clipboard-list',
                'patterns' => ['company.dailies.index', 'company.dailies.collaborator', 'company.dailies.*'],
            ],
            'clients' => [
                'route' => 'company.clients.index',
                'label' => 'Clientes',
                'icon' => 'fa-users',
                'patterns' => ['company.clients.*'],
            ],
            'contracts' => [
                'route' => 'company.contracts.index',
                'label' => 'Contratos',
                'icon' => 'fa-file-contract',
                'patterns' => ['company.contracts.*'],
            ],
            'receivables' => [
                'route' => 'company.receivables.index',
                'label' => 'Receber',
                'icon' => 'fa-arrow-circle-down',
                'patterns' => ['company.receivables.*'],
            ],
            'payables' => [
                'route' => 'company.payables.index',
                'label' => 'Pagar',
                'icon' => 'fa-arrow-circle-up',
                'patterns' => ['company.payables.*'],
            ],
            'leads' => [
                'route' => 'company.leads.index',
                'label' => 'Leads',
                'icon' => 'fa-bullseye',
                'patterns' => ['company.leads.*'],
            ],
            'employees' => [
                'route' => 'company.employees.index',
                'label' => 'Equipe',
                'icon' => 'fa-user-tie',
                'patterns' => ['company.employees.*'],
            ],
            'expenses' => [
                'route' => 'company.expenses.index',
                'label' => 'Despesas',
                'icon' => 'fa-receipt',
                'patterns' => ['company.expenses.*'],
            ],
            'suppliers' => [
                'route' => 'company.suppliers.index',
                'label' => 'Fornecedores',
                'icon' => 'fa-truck',
                'patterns' => ['company.suppliers.*'],
            ],
        ];
    }

    /**
     * @param  list<string>  $patterns
     * @return array{route: string, params: array, label: string, icon: string, patterns: list<string>, mobile: bool}
     */
    protected static function item(
        string $route,
        string $label,
        string $icon,
        array $patterns,
        bool $mobile = true,
    ): array {
        return [
            'route' => $route,
            'params' => [],
            'label' => $label,
            'icon' => $icon,
            'patterns' => $patterns,
            'mobile' => $mobile,
        ];
    }
}

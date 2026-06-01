<?php

namespace App\Services;

class TutorialPersonaService
{
    public function resolve(CompanyAuthorizationService $authz): string
    {
        if ($authz->isClient()) {
            return 'client';
        }

        if (in_array($authz->role(), ['owner', 'admin'], true)) {
            return 'admin';
        }

        if ($authz->canViewDeveloperDashboard()) {
            return 'developer';
        }

        if ($authz->canManage()) {
            return 'manager';
        }

        return 'collaborator';
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        return [
            'admin' => 'Administrador',
            'manager' => 'Gestor',
            'developer' => 'Programador',
            'collaborator' => 'Colaborador',
            'client' => 'Cliente',
        ];
    }
}

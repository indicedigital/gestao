<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyPermissionProfile;
use App\Support\PermissionModules;

class CompanyPermissionProfileService
{
    /** @return array<string, array{name: string, permissions: array}> */
    public function systemTemplates(): array
    {
        return [
            'diretor' => [
                'name' => 'Diretor',
                'permissions' => PermissionModules::directorTemplate(),
            ],
            'gestor' => [
                'name' => 'Gestor',
                'permissions' => PermissionModules::managerDefaults(),
            ],
            'programador' => [
                'name' => 'Programador',
                'permissions' => PermissionModules::developerTemplate(),
            ],
            'colaborador' => [
                'name' => 'Colaborador',
                'permissions' => PermissionModules::collaboratorDefaults(),
            ],
        ];
    }

    public function seedDefaultsForCompany(Company $company): void
    {
        foreach ($this->systemTemplates() as $slug => $template) {
            CompanyPermissionProfile::firstOrCreate(
                ['company_id' => $company->id, 'slug' => $slug],
                [
                    'name' => $template['name'],
                    'permissions' => $template['permissions'],
                    'is_system' => true,
                ]
            );
        }
    }

    public function ensureDefaults(Company $company): void
    {
        if (CompanyPermissionProfile::where('company_id', $company->id)->exists()) {
            return;
        }

        $this->seedDefaultsForCompany($company);
    }

    public function profileForCompany(Company $company, int $profileId): CompanyPermissionProfile
    {
        return CompanyPermissionProfile::where('company_id', $company->id)->findOrFail($profileId);
    }
}

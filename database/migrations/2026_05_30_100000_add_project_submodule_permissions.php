<?php

use App\Models\CompanyPermissionProfile;
use App\Support\PermissionModules;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $keys = ['project_overview', 'project_financial', 'project_dashboard'];

        CompanyPermissionProfile::query()->cursor()->each(function (CompanyPermissionProfile $profile) use ($keys) {
            $permissions = PermissionModules::normalizePermissions($profile->permissions);

            if (in_array($profile->slug, ['diretor', 'gestor'], true)) {
                foreach ($keys as $key) {
                    $permissions['modules'][$key] = true;
                }
            } elseif (in_array($profile->slug, ['programador', 'colaborador'], true)) {
                foreach ($keys as $key) {
                    $permissions['modules'][$key] = false;
                }
            }

            $profile->update(['permissions' => $permissions]);
        });
    }

    public function down(): void
    {
        // noop
    }
};

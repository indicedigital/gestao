<?php

use App\Models\CompanyPermissionProfile;
use App\Support\PermissionModules;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CompanyPermissionProfile::query()->cursor()->each(function (CompanyPermissionProfile $profile) {
            $permissions = PermissionModules::normalizePermissions($profile->permissions);

            if (in_array($profile->slug, ['programador', 'colaborador'], true)) {
                $permissions['modules']['developer_dashboard'] = true;
            }

            $profile->update(['permissions' => $permissions]);
        });
    }

    public function down(): void
    {
        // noop
    }
};

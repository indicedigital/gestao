<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_permission_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
            $table->index('company_id');
        });

        Schema::table('user_company', function (Blueprint $table) {
            if (! Schema::hasColumn('user_company', 'permission_profile_id')) {
                $table->foreignId('permission_profile_id')
                    ->nullable()
                    ->after('employee_id')
                    ->constrained('company_permission_profiles')
                    ->nullOnDelete();
            }
        });

        if (class_exists(\App\Models\Company::class)) {
            $service = app(\App\Services\CompanyPermissionProfileService::class);
            foreach (\App\Models\Company::query()->cursor() as $company) {
                $service->seedDefaultsForCompany($company);
            }
        }
    }

    public function down(): void
    {
        Schema::table('user_company', function (Blueprint $table) {
            if (Schema::hasColumn('user_company', 'permission_profile_id')) {
                $table->dropConstrainedForeignId('permission_profile_id');
            }
        });

        Schema::dropIfExists('company_permission_profiles');
    }
};

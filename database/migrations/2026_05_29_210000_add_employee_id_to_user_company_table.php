<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_company', function (Blueprint $table) {
            if (! Schema::hasColumn('user_company', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->after('client_id')
                    ->constrained('employees')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_company', function (Blueprint $table) {
            if (Schema::hasColumn('user_company', 'employee_id')) {
                $table->dropConstrainedForeignId('employee_id');
            }
        });
    }
};

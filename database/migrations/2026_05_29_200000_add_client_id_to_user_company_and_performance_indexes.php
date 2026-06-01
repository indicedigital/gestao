<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_company', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('completed_at');
            $table->index(['company_id', 'assignee_id', 'status']);
        });

        Schema::table('dailies', function (Blueprint $table) {
            $table->index('project_id');
            $table->index(['company_id', 'work_date', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('dailies', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'work_date', 'employee_id']);
            $table->dropIndex(['project_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'assignee_id', 'status']);
            $table->dropIndex(['completed_at']);
        });

        Schema::table('user_company', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};

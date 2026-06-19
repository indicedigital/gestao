<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('daily_hours_goal', 5, 2)->nullable()->after('notes');
            $table->decimal('monthly_hours_goal', 8, 2)->nullable()->after('daily_hours_goal');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['daily_hours_goal', 'monthly_hours_goal']);
        });
    }
};

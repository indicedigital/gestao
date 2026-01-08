<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Configuração de data de vencimento para contratos recorrentes
            $table->string('recurring_due_date_type')->nullable()->after('billing_period');
            // last_business_day, first_business_day, fifth_business_day, fixed_day
            $table->integer('recurring_due_date_day')->nullable()->after('recurring_due_date_type');
            // Dia fixo (1-31) quando type = fixed_day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['recurring_due_date_type', 'recurring_due_date_day']);
        });
    }
};

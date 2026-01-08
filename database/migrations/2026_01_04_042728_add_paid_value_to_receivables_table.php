<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->decimal('paid_value', 15, 2)->nullable()->after('value');
        });
        
        // Popula o campo paid_value com o valor total para registros já pagos
        DB::table('receivables')
            ->where('status', 'paid')
            ->update(['paid_value' => DB::raw('value')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->dropColumn('paid_value');
        });
    }
};

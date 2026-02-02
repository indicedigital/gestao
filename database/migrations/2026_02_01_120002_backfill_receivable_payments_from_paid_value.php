<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $receivables = DB::table('receivables')
            ->whereNotNull('paid_date')
            ->where('paid_value', '>', 0)
            ->get();

        foreach ($receivables as $r) {
            DB::table('receivable_payments')->insert([
                'receivable_id' => $r->id,
                'amount' => $r->paid_value,
                'paid_date' => $r->paid_date,
                'payment_method' => $r->payment_method,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Não remove dados; down da tabela já remove os payments
    }
};

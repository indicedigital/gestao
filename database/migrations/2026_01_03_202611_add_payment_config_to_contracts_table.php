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
        // Adiciona campos de configuração de pagamento na tabela contracts
        Schema::table('contracts', function (Blueprint $table) {
            // Configuração de pagamento para contratos fixos
            $table->boolean('has_down_payment')->default(false)->after('value'); // Tem entrada?
            $table->decimal('down_payment_value', 15, 2)->nullable()->after('has_down_payment'); // Valor da entrada
            $table->date('down_payment_date')->nullable()->after('down_payment_value'); // Data da entrada
            
            // Configuração de parcelas
            $table->integer('installments_count')->nullable()->after('down_payment_date'); // Número de parcelas
            $table->string('payment_frequency')->nullable()->after('installments_count'); // daily, weekly, biweekly, monthly, quarterly, yearly
            $table->boolean('equal_installments')->default(true)->after('payment_frequency'); // Parcelas iguais?
            $table->date('first_installment_date')->nullable()->after('equal_installments'); // Data da primeira parcela
        });

        // Cria tabela de parcelas do contrato
        Schema::create('contract_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->integer('installment_number'); // Número da parcela (1, 2, 3...)
            $table->string('description')->nullable(); // Descrição da parcela
            $table->decimal('value', 15, 2); // Valor da parcela
            $table->date('due_date'); // Data de vencimento
            $table->date('paid_date')->nullable(); // Data de pagamento
            $table->string('status')->default('pending'); // pending, paid, overdue, cancelled
            $table->string('payment_method')->nullable(); // Forma de pagamento
            $table->text('notes')->nullable(); // Observações
            $table->timestamps();
            $table->softDeletes();

            $table->index('contract_id');
            $table->index('status');
            $table->index('due_date');
            $table->unique(['contract_id', 'installment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_installments');
        
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'has_down_payment',
                'down_payment_value',
                'down_payment_date',
                'installments_count',
                'payment_frequency',
                'equal_installments',
                'first_installment_date',
            ]);
        });
    }
};

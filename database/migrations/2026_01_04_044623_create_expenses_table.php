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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('type'); // fixed, variable
            $table->string('description');
            $table->decimal('value', 15, 2);
            $table->string('category')->nullable(); // utilities, rent, services, supplies, etc
            $table->string('supplier_name')->nullable();
            $table->integer('due_date_day')->nullable(); // Dia do mês para despesas fixas (1-31)
            $table->date('due_date')->nullable(); // Data de vencimento para despesas variáveis
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('company_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('fixed'); // fixed (fechado), recurring (recorrente)
            $table->decimal('total_value', 15, 2)->default(0);
            $table->integer('installments')->default(1); // Número de parcelas
            $table->string('status')->default('planning'); // planning, in_progress, paused, completed, cancelled
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('cost', 15, 2)->default(0); // Custo total do projeto
            $table->decimal('profit_margin', 5, 2)->nullable(); // Margem de lucro
            $table->text('scope')->nullable(); // Escopo do projeto
            $table->json('deliverables')->nullable(); // Entregas do projeto
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('company_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

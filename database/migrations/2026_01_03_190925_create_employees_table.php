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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('clt'); // clt, pj, freelancer
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('document')->nullable(); // CPF
            $table->string('position')->nullable(); // Cargo
            $table->string('role')->nullable(); // Função
            $table->date('hire_date')->nullable();
            $table->date('dismissal_date')->nullable();
            $table->decimal('salary', 15, 2)->nullable(); // Salário base
            $table->string('status')->default('active'); // active, inactive, dismissed
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('company_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

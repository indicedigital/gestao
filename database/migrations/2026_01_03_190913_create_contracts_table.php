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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('type'); // client_recurring, client_fixed, employee_clt, employee_pj
            $table->string('number')->unique()->nullable(); // Número do contrato
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->string('billing_period')->nullable(); // monthly, yearly
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->string('status')->default('active'); // active, suspended, cancelled, expired
            $table->json('terms')->nullable(); // Termos e condições
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('company_id');
            $table->index('client_id');
            $table->index('employee_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

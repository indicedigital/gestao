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
        Schema::create('fiscal_entry_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('person_type', 8)->default('pf'); // pf | pj
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('document')->nullable();
            $table->string('document_type', 8)->nullable(); // cpf | cnpj
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 4)->nullable();
            $table->string('zip_code', 16)->nullable();
            $table->string('country')->default('Brasil');
            $table->decimal('amount_received', 15, 2);
            $table->date('received_date');
            $table->boolean('is_issued')->default(false);
            $table->date('issued_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'received_date']);
            $table->index(['company_id', 'is_issued']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_entry_notes');
    }
};

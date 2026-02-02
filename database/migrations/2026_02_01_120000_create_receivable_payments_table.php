<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receivable_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('paid_date');
            $table->string('payment_method')->nullable();
            $table->timestamps();

            $table->index('receivable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_sla_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('priority'); // P0, P1, P2, P3
            $table->unsignedInteger('hours'); // SLA em horas
            $table->timestamps();

            $table->unique(['contract_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sla_settings');
    }
};

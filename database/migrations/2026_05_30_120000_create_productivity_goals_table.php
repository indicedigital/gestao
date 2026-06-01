<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productivity_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period_type', 20)->default('daily');
            $table->decimal('hours_target', 8, 2)->default(8);
            $table->unsignedSmallInteger('tasks_target')->default(2);
            $table->decimal('completion_rate_target', 5, 2)->default(75);
            $table->timestamps();

            $table->unique(['company_id', 'employee_id', 'period_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productivity_goals');
    }
};

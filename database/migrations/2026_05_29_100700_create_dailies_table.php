<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subtask_id')->nullable()->constrained()->nullOnDelete();
            $table->date('work_date');
            $table->text('description');
            $table->decimal('hours', 8, 2);
            $table->text('blockers')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'work_date']);
            $table->index(['user_id', 'work_date']);
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dailies');
    }
};

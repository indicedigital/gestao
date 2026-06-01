<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('todo');
            $table->date('due_date')->nullable();
            $table->decimal('hours_spent', 8, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['task_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_type')->default('internal'); // internal, client
            $table->string('requester_name')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('support');
            $table->string('priority')->default('P2');
            $table->string('status')->default('backlog');
            $table->string('creation_channel')->default('system'); // system, client, api, automation
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->unsignedInteger('sla_hours')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index('assignee_id');
            $table->index('sla_deadline');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

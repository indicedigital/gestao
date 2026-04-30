<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date')->nullable();
            $table->string('project_name');
            $table->text('brief_description')->nullable();
            $table->json('project_scopes')->nullable();
            $table->string('project_scope_other')->nullable();
            $table->json('app_platforms')->nullable();
            $table->string('project_kind');
            $table->string('project_stage')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('has_domain')->default(false);
            $table->string('domain_info')->nullable();
            $table->boolean('has_server')->default(false);
            $table->string('server_info')->nullable();
            $table->decimal('expected_budget', 12, 2)->nullable();
            $table->date('expected_deadline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};


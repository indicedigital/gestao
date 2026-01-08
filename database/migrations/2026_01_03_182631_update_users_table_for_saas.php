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
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('password');
            $table->boolean('is_super_admin')->default(false)->after('status');
            $table->foreignId('company_id')->nullable()->after('is_super_admin')->constrained()->onDelete('set null');
            $table->string('profile_photo_path', 2048)->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['status', 'is_super_admin', 'company_id', 'profile_photo_path']);
        });
    }
};

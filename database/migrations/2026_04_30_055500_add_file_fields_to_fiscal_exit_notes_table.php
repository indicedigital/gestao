<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_exit_notes', function (Blueprint $table) {
            $table->string('document_file_path')->nullable()->after('issued_at');
            $table->string('document_file_original_name')->nullable()->after('document_file_path');
            $table->string('document_file_mime', 120)->nullable()->after('document_file_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_exit_notes', function (Blueprint $table) {
            $table->dropColumn([
                'document_file_path',
                'document_file_original_name',
                'document_file_mime',
            ]);
        });
    }
};

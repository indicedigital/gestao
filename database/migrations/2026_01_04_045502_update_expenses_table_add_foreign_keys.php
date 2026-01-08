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
        Schema::table('expenses', function (Blueprint $table) {
            // Remove a coluna category (string) e supplier_name (string)
            // Adiciona foreign keys para expense_category_id e supplier_id
            $table->dropColumn(['category', 'supplier_name']);
            
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            
            $table->index('expense_category_id');
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['expense_category_id', 'supplier_id']);
            
            $table->string('category')->nullable();
            $table->string('supplier_name')->nullable();
        });
    }
};

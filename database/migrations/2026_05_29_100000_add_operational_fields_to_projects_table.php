<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('category')->default('desenvolvimento')->after('type');
        });

        DB::table('projects')->where('status', 'planning')->update(['status' => 'implementing']);
        DB::table('projects')->where('status', 'in_progress')->update(['status' => 'active']);
    }

    public function down(): void
    {
        DB::table('projects')->where('status', 'implementing')->update(['status' => 'planning']);
        DB::table('projects')->where('status', 'active')->update(['status' => 'in_progress']);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};

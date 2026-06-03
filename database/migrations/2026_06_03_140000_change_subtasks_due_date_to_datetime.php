<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subtasks') || ! Schema::hasColumn('subtasks', 'due_date')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE subtasks MODIFY due_date DATETIME NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE subtasks ALTER COLUMN due_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING due_date::timestamp');
        } else {
            // sqlite / outros: recria via schema builder não suporta change fácil; mantém date
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('subtasks')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE subtasks MODIFY due_date DATE NULL');
        }
    }
};

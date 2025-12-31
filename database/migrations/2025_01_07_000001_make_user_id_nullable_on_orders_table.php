<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN user_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN user_id SET NOT NULL');
        }
    }
};

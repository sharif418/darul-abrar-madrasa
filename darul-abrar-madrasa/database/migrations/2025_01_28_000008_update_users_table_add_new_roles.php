<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $roles = [
            'admin',
            'teacher',
            'student',
            'staff',
            'guardian',
            'accountant',
            'librarian',
            'hostel_manager',
            'transport_manager',
            'reception',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL/MariaDB enum alteration
            $enumList = "'" . implode("','", $roles) . "'";
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM($enumList) NOT NULL DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: drop and recreate constraint
            // Determine current enum type name if exists, or use a new one
            // Using a simple approach: drop constraint and use CHECK with IN list
            $enumList = "'" . implode("','", $roles) . "'";
            try {
                DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            } catch (\Throwable $e) {
                // ignore
            }
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ($enumList))");
        } else {
            // sqlite/others: cannot alter enum, ensure values via application layer
            // No-op
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        // Revert to original list (best-effort; adjust as needed)
        $original = [
            'admin',
            'teacher',
            'student',
            'staff',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $enumList = "'" . implode("','", $original) . "'";
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM($enumList) NOT NULL DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            $enumList = "'" . implode("','", $original) . "'";
            try {
                DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            } catch (\Throwable $e) {
                // ignore
            }
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ($enumList))");
        } else {
            // No-op for sqlite/others
        }
    }
};

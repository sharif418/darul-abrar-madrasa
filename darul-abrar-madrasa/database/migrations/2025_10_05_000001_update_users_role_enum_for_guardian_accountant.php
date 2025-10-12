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
            // Ensure enum includes new roles (guardian, accountant, etc.)
            $enumList = "'" . implode("','", $roles) . "'";
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM($enumList) NOT NULL DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            $enumList = "'" . implode("','", $roles) . "'";
            try {
                DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            } catch (\Throwable $e) {
                // ignore
            }
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ($enumList))");
        } else {
            // sqlite/others: no-op; enforced at application layer
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

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

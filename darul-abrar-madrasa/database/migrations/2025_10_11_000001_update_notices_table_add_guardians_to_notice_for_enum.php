<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('notices')) {
            $enumValues = ['all', 'students', 'teachers', 'staff', 'guardians'];
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                $enumList = implode("','", $enumValues);
                DB::statement("ALTER TABLE `notices` MODIFY COLUMN `notice_for` ENUM('{$enumList}') NOT NULL");
            } elseif ($driver === 'pgsql') {
                // Drop all existing check constraints on notice_for column
                $constraints = DB::select("
                    SELECT con.conname
                    FROM pg_constraint con
                    INNER JOIN pg_class rel ON rel.oid = con.conrelid
                    INNER JOIN pg_attribute att ON att.attrelid = con.conrelid AND att.attnum = ANY(con.conkey)
                    WHERE rel.relname = 'notices'
                    AND att.attname = 'notice_for'
                    AND con.contype = 'c'
                ");
                
                foreach ($constraints as $constraint) {
                    try {
                        DB::statement("ALTER TABLE notices DROP CONSTRAINT IF EXISTS {$constraint->conname}");
                    } catch (\Exception $e) {
                        // Continue if constraint doesn't exist
                    }
                }
                
                // Add new constraint with guardians included
                $enumList = implode("','", $enumValues);
                DB::statement("ALTER TABLE notices ADD CONSTRAINT notices_notice_for_check CHECK (notice_for IN ('{$enumList}'))");
            } else {
                // SQLite: Enum constraints are enforced at application layer via validation rules
                // SQLite does not support ALTER COLUMN, so we skip database-level changes
                // The application-layer validation in StoreNoticeRequest and UpdateNoticeRequest
                // will enforce the enum values including 'guardians'
                Log::info('SQLite detected: notice_for enum update skipped. Application-layer validation will enforce values.');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notices')) {
            $enumValues = ['all', 'students', 'teachers', 'staff'];
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                // Update any existing 'guardians' values to 'all' before restricting enum
                DB::statement("UPDATE `notices` SET `notice_for` = 'all' WHERE `notice_for` = 'guardians'");
                
                $enumList = implode("','", $enumValues);
                DB::statement("ALTER TABLE `notices` MODIFY COLUMN `notice_for` ENUM('{$enumList}') NOT NULL");
            } elseif ($driver === 'pgsql') {
                // Update any existing 'guardians' values to 'all' before restricting constraint
                DB::statement("UPDATE notices SET notice_for = 'all' WHERE notice_for = 'guardians'");
                
                // Drop all existing check constraints on notice_for column
                $constraints = DB::select("
                    SELECT con.conname
                    FROM pg_constraint con
                    INNER JOIN pg_class rel ON rel.oid = con.conrelid
                    INNER JOIN pg_attribute att ON att.attrelid = con.conrelid AND att.attnum = ANY(con.conkey)
                    WHERE rel.relname = 'notices'
                    AND att.attname = 'notice_for'
                    AND con.contype = 'c'
                ");
                
                foreach ($constraints as $constraint) {
                    try {
                        DB::statement("ALTER TABLE notices DROP CONSTRAINT IF EXISTS {$constraint->conname}");
                    } catch (\Exception $e) {
                        // Continue if constraint doesn't exist
                    }
                }
                
                // Add constraint without guardians
                $enumList = implode("','", $enumValues);
                DB::statement("ALTER TABLE notices ADD CONSTRAINT notices_notice_for_check CHECK (notice_for IN ('{$enumList}'))");
            } else {
                // SQLite: No database-level changes needed
                // Application-layer validation will revert to original values
                Log::info('SQLite detected: notice_for enum rollback skipped. Application-layer validation handles enforcement.');
            }
        }
    }
};

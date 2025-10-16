<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fees')) {
            return;
        }

        // Ensure invoice_number column exists first (to avoid AFTER invoice_number errors)
        if (!Schema::hasColumn('fees', 'invoice_number')) {
            Schema::table('fees', function (Blueprint $table) {
                // Use a reasonable length for invoice numbers (e.g., INV-YYYY-MM-XXXXX)
                $table->string('invoice_number', 64)->nullable()->after('id');
            });
        }

        // Add audit columns if they do not exist
        Schema::table('fees', function (Blueprint $table) {
            if (!Schema::hasColumn('fees', 'invoice_generated_at')) {
                // Not all drivers support "after", so don't rely on it
                $table->timestamp('invoice_generated_at')->nullable();
            }
            if (!Schema::hasColumn('fees', 'invoice_generated_by')) {
                // Use plain unsignedBigInteger to avoid early FK dependency on users table
                $table->unsignedBigInteger('invoice_generated_by')->nullable();
            }
        });

        // Add unique index on invoice_number if it does not exist (only if column exists)
        if (Schema::hasColumn('fees', 'invoice_number')) {
            $driver = DB::getDriverName();
            try {
                if ($driver === 'mysql') {
                    $indexExists = DB::selectOne("
                        SELECT COUNT(1) AS cnt
                        FROM information_schema.statistics
                        WHERE table_schema = DATABASE()
                          AND table_name = 'fees'
                          AND index_name = 'fees_invoice_number_unique'
                    ");
                    if (!$indexExists || (int)$indexExists->cnt === 0) {
                        DB::statement("ALTER TABLE `fees` ADD UNIQUE `fees_invoice_number_unique` (`invoice_number`)");
                    }
                } elseif ($driver === 'pgsql') {
                    $indexExists = DB::selectOne("
                        SELECT COUNT(1) AS cnt
                        FROM pg_indexes
                        WHERE schemaname = ANY (current_schemas(false))
                          AND tablename = 'fees'
                          AND indexname = 'fees_invoice_number_unique'
                    ");
                    if (!$indexExists || (int)$indexExists->cnt === 0) {
                        DB::statement('CREATE UNIQUE INDEX "fees_invoice_number_unique" ON "fees" ("invoice_number")');
                    }
                } else {
                    // Fallback for sqlite and others: attempt schema builder unique; ignore if already exists
                    Schema::table('fees', function (Blueprint $table) {
                        try {
                            $table->unique('invoice_number', 'fees_invoice_number_unique');
                        } catch (\Throwable $e) {
                            // ignore if exists
                        }
                    });
                }
            } catch (\Throwable $e) {
                // As a safeguard, attempt via schema builder (may no-op if exists)
                try {
                    Schema::table('fees', function (Blueprint $table) {
                        $table->unique('invoice_number', 'fees_invoice_number_unique');
                    });
                } catch (\Throwable $ignored) {
                    // ignore
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fees')) {
            // Drop unique index if exists
            $driver = DB::getDriverName();
            try {
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE `fees` DROP INDEX `fees_invoice_number_unique`");
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS "fees_invoice_number_unique"');
                } else {
                    Schema::table('fees', function (Blueprint $table) {
                        try {
                            $table->dropUnique('fees_invoice_number_unique');
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    });
                }
            } catch (\Throwable $ignored) {
                // ignore
            }

            Schema::table('fees', function (Blueprint $table) {
                if (Schema::hasColumn('fees', 'invoice_generated_by')) {
                    // Drop FK then column
                    try {
                        $table->dropConstrainedForeignId('invoice_generated_by');
                    } catch (\Throwable $e) {
                        try {
                            $table->dropForeign(['invoice_generated_by']);
                        } catch (\Throwable $e2) {
                            // ignore
                        }
                        $table->dropColumn('invoice_generated_by');
                    }
                }

                if (Schema::hasColumn('fees', 'invoice_generated_at')) {
                    $table->dropColumn('invoice_generated_at');
                }
            });
        }
    }
};

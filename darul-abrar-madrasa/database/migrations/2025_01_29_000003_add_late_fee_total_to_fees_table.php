<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a separate accumulator for late fees without mutating principal amount.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fees')) {
            return;
        }

        Schema::table('fees', function (Blueprint $table) {
            if (!Schema::hasColumn('fees', 'late_fee_total')) {
                $table->decimal('late_fee_total', 10, 2)->default(0)->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('fees')) {
            return;
        }

        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'late_fee_total')) {
                $table->dropColumn('late_fee_total');
            }
        });
    }
};

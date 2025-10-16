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
        // Add missing fields to exams table (guard if table exists)
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!Schema::hasColumn('exams', 'pass_gpa')) {
                    $table->decimal('pass_gpa', 3, 2)->default(0)->after('is_result_published');
                }
                if (!Schema::hasColumn('exams', 'fail_limit')) {
                    $table->integer('fail_limit')->default(0)->after('pass_gpa');
                }
            });
        }

        // Add missing fields to results table (guard if table exists)
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (!Schema::hasColumn('results', 'gpa_point')) {
                    $table->decimal('gpa_point', 3, 2)->default(0)->after('grade');
                }
                if (!Schema::hasColumn('results', 'is_passed')) {
                    $table->boolean('is_passed')->default(false)->after('gpa_point');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from exams table (guard if table exists)
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (Schema::hasColumn('exams', 'pass_gpa')) {
                    $table->dropColumn('pass_gpa');
                }
                if (Schema::hasColumn('exams', 'fail_limit')) {
                    $table->dropColumn('fail_limit');
                }
            });
        }

        // Remove fields from results table (guard if table exists)
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (Schema::hasColumn('results', 'gpa_point')) {
                    $table->dropColumn('gpa_point');
                }
                if (Schema::hasColumn('results', 'is_passed')) {
                    $table->dropColumn('is_passed');
                }
            });
        }
    }
};

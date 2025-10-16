<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix database consistency issues:
     * 1. Standardize relationship column naming
     * 2. Update ENUM values to industry standard
     * 3. Add proper indexes
     */
    public function up(): void
    {
        // Step 1: Update guardian_student table - rename 'relationship' to 'relationship_type'
        // and update ENUM values to match industry standards
        if (Schema::hasTable('guardian_student')) {
            // Check if column exists before renaming
            if (Schema::hasColumn('guardian_student', 'relationship')) {
                // First, update the ENUM values
                DB::statement("ALTER TABLE guardian_student MODIFY COLUMN relationship ENUM('father', 'mother', 'legal_guardian', 'grandparent', 'uncle', 'aunt', 'sibling', 'other') NOT NULL DEFAULT 'other'");
                
                // Then rename the column
                Schema::table('guardian_student', function (Blueprint $table) {
                    $table->renameColumn('relationship', 'relationship_type');
                });
            }
        }

        // Step 2: Update guardians table ENUM values to match
        if (Schema::hasTable('guardians')) {
            if (Schema::hasColumn('guardians', 'relationship_type')) {
                DB::statement("ALTER TABLE guardians MODIFY COLUMN relationship_type ENUM('father', 'mother', 'legal_guardian', 'grandparent', 'uncle', 'aunt', 'sibling', 'other') NOT NULL DEFAULT 'other'");
            }
        }

        // Step 3: Add indexes for better performance
        Schema::table('guardian_student', function (Blueprint $table) {
            // Add index on relationship_type for faster queries
            if (!$this->indexExists('guardian_student', 'guardian_student_relationship_type_index')) {
                $table->index('relationship_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes
        if (Schema::hasTable('guardian_student')) {
            if (Schema::hasColumn('guardian_student', 'relationship_type')) {
                // Rename back
                Schema::table('guardian_student', function (Blueprint $table) {
                    $table->renameColumn('relationship_type', 'relationship');
                });
                
                // Revert ENUM values
                DB::statement("ALTER TABLE guardian_student MODIFY COLUMN relationship ENUM('father', 'mother', 'legal_guardian', 'sibling', 'other') NOT NULL DEFAULT 'other'");
            }
        }

        if (Schema::hasTable('guardians')) {
            if (Schema::hasColumn('guardians', 'relationship_type')) {
                DB::statement("ALTER TABLE guardians MODIFY COLUMN relationship_type ENUM('father', 'mother', 'legal_guardian', 'other') NOT NULL DEFAULT 'other'");
            }
        }

        // Remove indexes
        Schema::table('guardian_student', function (Blueprint $table) {
            if ($this->indexExists('guardian_student', 'guardian_student_relationship_type_index')) {
                $table->dropIndex('guardian_student_relationship_type_index');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return !empty($indexes);
    }
};

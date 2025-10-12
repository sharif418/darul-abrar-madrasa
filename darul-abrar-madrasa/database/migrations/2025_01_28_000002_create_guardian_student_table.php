<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('guardian_student')) {
            Schema::create('guardian_student', function (Blueprint $table) {
                $table->bigIncrements('id');
            $table->unsignedBigInteger('guardian_id');
            $table->unsignedBigInteger('student_id');
                $table->enum('relationship', ['father', 'mother', 'legal_guardian', 'sibling', 'other']);
                $table->boolean('is_primary_guardian')->default(false);
                $table->boolean('can_pickup')->default(true);
                $table->boolean('financial_responsibility')->default(false);
                $table->boolean('receive_notifications')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['guardian_id', 'student_id'], 'guardian_student_unique');
                $table->index('guardian_id');
                $table->index('student_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};

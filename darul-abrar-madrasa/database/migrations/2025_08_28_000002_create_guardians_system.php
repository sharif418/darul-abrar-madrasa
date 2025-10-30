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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 15);
            $table->string('national_id')->nullable();
            $table->string('occupation')->nullable();
            $table->string('designation')->nullable();
            $table->string('office_address')->nullable();
            $table->text('present_address');
            $table->text('permanent_address')->nullable();
            $table->decimal('annual_income', 12, 2)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['phone', 'email']);
        });

        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('guardian_id')->constrained()->onDelete('cascade');
            $table->enum('relationship', ['father', 'mother', 'brother', 'sister', 'uncle', 'aunt', 'grandfather', 'grandmother', 'other']);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->boolean('receive_communication')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'guardian_id']);
            $table->index(['student_id', 'is_primary']);
        });

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone', 15);
            $table->string('relation');
            $table->text('address');
            $table->integer('priority')->default(1);
            $table->timestamps();

            $table->index(['student_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('guardians');
    }
};

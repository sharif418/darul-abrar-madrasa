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
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['birth_certificate', 'previous_certificate', 'transfer_certificate', 'photo', 'national_id', 'other']);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_size')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'document_type']);
        });

        Schema::create('student_medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->text('special_needs')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone', 15)->nullable();
            $table->string('hospital_name')->nullable();
            $table->text('health_insurance')->nullable();
            $table->timestamps();

            $table->index('student_id');
        });

        Schema::create('student_previous_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('school_name');
            $table->string('class_grade');
            $table->year('passing_year')->nullable();
            $table->string('result')->nullable();
            $table->string('board')->nullable();
            $table->text('reason_for_leaving')->nullable();
            $table->timestamps();

            $table->index('student_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('nationality')->default('Bangladeshi')->after('gender');
            $table->string('religion')->nullable()->after('nationality');
            $table->string('mother_tongue')->nullable()->after('religion');
            $table->boolean('transport_required')->default(false)->after('blood_group');
            $table->string('transport_route')->nullable()->after('transport_required');
            $table->boolean('hostel_required')->default(false)->after('transport_route');
            $table->text('previous_school')->nullable()->after('hostel_required');
            $table->text('admission_reason')->nullable()->after('previous_school');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nationality',
                'religion',
                'mother_tongue',
                'transport_required',
                'transport_route',
                'hostel_required',
                'previous_school',
                'admission_reason'
            ]);
        });

        Schema::dropIfExists('student_previous_education');
        Schema::dropIfExists('student_medical_records');
        Schema::dropIfExists('student_documents');
    }
};

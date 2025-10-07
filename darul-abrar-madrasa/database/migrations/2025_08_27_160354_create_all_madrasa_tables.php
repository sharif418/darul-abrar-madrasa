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
        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'teacher', 'student', 'staff'])->default('student');
            $table->string('avatar')->nullable();
            $table->string('phone', 15)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create password_reset_tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Create sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create classes table
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('class_numeric')->nullable();
            $table->string('section')->nullable();
            $table->integer('capacity')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create teachers table
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('designation');
            $table->string('qualification');
            // Phone is stored on users table in current architecture; keep teacher.phone nullable to avoid constraint issues
            $table->string('phone', 15)->nullable();
            $table->string('address');
            $table->date('joining_date');
            $table->decimal('salary', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create students table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->string('roll_number')->nullable();
            $table->string('admission_number')->unique();
            $table->date('admission_date');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('guardian_phone', 15);
            $table->string('guardian_email')->nullable();
            $table->string('address');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('blood_group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create subjects table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('full_mark')->default(100);
            $table->integer('pass_mark')->default(33);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create attendances table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave']);
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate attendance records
            $table->unique(['student_id', 'date']);
        });

        // Create fees table
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('fee_type'); // admission, monthly, exam, etc.
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['paid', 'unpaid', 'partial'])->default('unpaid');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Create exams table
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_result_published')->default(false);
            $table->timestamps();
        });

        // Create results table
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->decimal('marks_obtained', 5, 2);
            $table->string('grade')->nullable();
            // Added to support model calculations
            $table->decimal('gpa_point', 3, 2)->default(0);
            $table->boolean('is_passed')->default(false);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate results
            $table->unique(['student_id', 'exam_id', 'subject_id']);
        });

        // Create notices table
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->enum('notice_for', ['all', 'students', 'teachers', 'staff']);
            $table->boolean('is_active')->default(true);
            $table->foreignId('published_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
        Schema::dropIfExists('results');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
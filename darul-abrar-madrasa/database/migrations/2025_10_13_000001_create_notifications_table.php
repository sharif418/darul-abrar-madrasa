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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // low_attendance, poor_performance, fee_due, exam_schedule, result_published
            $table->enum('channel', ['email', 'sms', 'both']);
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_type')->nullable(); // Guardian, Student, etc.
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('subject')->nullable();
            $table->text('message');
            $table->json('data')->nullable(); // Additional context data
            $table->enum('status', ['pending', 'sent', 'failed', 'queued'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes for performance
            $table->index(['type', 'status']);
            $table->index('recipient_id');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};

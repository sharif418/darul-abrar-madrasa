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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Notification type
            $table->enum('channel', ['email', 'sms']); // Channel
            $table->string('name'); // Template name/identifier
            $table->string('subject')->nullable(); // Email subject (null for SMS)
            $table->text('body'); // Template body with placeholders
            $table->json('available_variables')->nullable(); // Available placeholders
            $table->boolean('is_active')->default(true); // Enable/disable
            $table->timestamps();

            // Unique constraint: one template per type/channel
            $table->unique(['type', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};

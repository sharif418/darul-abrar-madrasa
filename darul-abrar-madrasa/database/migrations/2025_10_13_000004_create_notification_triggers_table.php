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
        Schema::create('notification_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Trigger type
            $table->string('name'); // Display name
            $table->text('description')->nullable(); // Admin reference
            $table->boolean('is_enabled')->default(true); // Enable/disable
            $table->json('conditions')->nullable(); // Trigger conditions
            $table->enum('frequency', ['immediate', 'daily', 'weekly'])->default('immediate');
            $table->timestamps();

            // Unique constraint: one config per trigger type
            $table->unique('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_triggers');
    }
};

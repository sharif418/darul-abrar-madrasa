<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Define column without FK constraint to avoid early reference issues. FK can be added later.
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('national_id')->nullable();
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->index();
            $table->string('alternative_phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('relationship_type', ['father', 'mother', 'legal_guardian', 'other'])->default('other');
            $table->boolean('is_primary_contact')->default(true);
            $table->boolean('emergency_contact')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // user_id is unique to ensure one guardian record per user account
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('late_fee_policies')) {
            Schema::create('late_fee_policies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('fee_type')->nullable(); // null => global policy
                $table->integer('grace_period_days')->default(0);
                $table->enum('calculation_type', ['fixed', 'percentage', 'daily', 'weekly']);
                $table->decimal('amount', 10, 2);
                $table->decimal('max_late_fee', 10, 2)->nullable();
                $table->boolean('compound')->default(false);
                $table->boolean('exclude_holidays')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index('fee_type');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('late_fee_policies');
    }
};

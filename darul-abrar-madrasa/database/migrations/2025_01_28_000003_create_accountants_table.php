<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accountants')) {
            Schema::create('accountants', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('employee_id')->unique();
                $table->string('designation');
                $table->string('qualification')->nullable();
                $table->string('phone');
                $table->text('address');
                $table->date('joining_date');
                $table->decimal('salary', 10, 2)->default(0);
                $table->boolean('can_approve_waivers')->default(false);
                $table->boolean('can_approve_refunds')->default(false);
                $table->decimal('max_waiver_amount', 10, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accountants');
    }
};

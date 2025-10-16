<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_waivers')) {
            Schema::create('fee_waivers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('fee_id')->nullable();
                $table->enum('waiver_type', ['scholarship', 'financial_aid', 'merit', 'sibling_discount', 'staff_child', 'other']);
                $table->enum('amount_type', ['percentage', 'fixed']);
                $table->decimal('amount', 10, 2);
                $table->text('reason');
                $table->date('valid_from');
                $table->date('valid_until')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index('student_id');
                $table->index('fee_id');
                $table->index('status');
                $table->index(['valid_from', 'valid_until']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_waivers');
    }
};

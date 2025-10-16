<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_installments')) {
            Schema::create('fee_installments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('fee_id');
                $table->integer('installment_number');
                $table->decimal('amount', 10, 2);
                $table->date('due_date');
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->date('payment_date')->nullable();
                $table->enum('status', ['pending', 'paid', 'overdue', 'waived'])->default('pending');
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->decimal('late_fee_applied', 10, 2)->default(0);
                $table->unsignedBigInteger('collected_by')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index('fee_id');
                $table->index('status');
                $table->index('due_date');
                $table->unique(['fee_id', 'installment_number'], 'fee_installments_unique_per_fee');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_installments');
    }
};

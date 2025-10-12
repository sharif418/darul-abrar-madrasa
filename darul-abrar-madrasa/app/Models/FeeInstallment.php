<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_id',
        'installment_number',
        'amount',
        'due_date',
        'paid_amount',
        'payment_date',
        'status',
        'payment_method',
        'transaction_id',
        'late_fee_applied',
        'collected_by',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'late_fee_applied' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'pending')->where('due_date', '<', now()->toDateString());
              });
        });
    }

    public function scopeForFee($query, int $feeId)
    {
        return $query->where('fee_id', $feeId);
    }

    public function scopeDueWithin($query, int $days)
    {
        $from = now()->toDateString();
        $to = now()->addDays($days)->toDateString();
        return $query->whereBetween('due_date', [$from, $to]);
    }

    /**
     * Accessors
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)$this->amount - (float)$this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getTotalAmountAttribute(): float
    {
        return (float)$this->amount + (float)$this->late_fee_applied;
    }

    /**
     * Helpers
     */
    public function markAsPaid(float $paidAmount, string $paymentMethod, ?string $transactionId, ?int $collectedBy): void
    {
        $this->paid_amount = min($this->amount, $paidAmount);
        $this->payment_method = $paymentMethod;
        $this->transaction_id = $transactionId;
        $this->payment_date = now()->toDateString();
        $this->status = ($this->paid_amount >= $this->amount) ? 'paid' : 'pending';
        $this->collected_by = $collectedBy;
        $this->save();
    }

    public function applyLateFee(float $amount): void
    {
        $this->late_fee_applied = (float)$this->late_fee_applied + (float)$amount;
        $this->save();
    }

    public function canBePaid(): bool
    {
        // Enforce sequential payment by default:
        if (!$this->relationLoaded('fee')) {
            $this->load('fee');
        }
        $previous = self::query()
            ->where('fee_id', $this->fee_id)
            ->where('installment_number', '<', $this->installment_number)
            ->where('status', '!=', 'paid')
            ->exists();

        return !$previous;
    }
}

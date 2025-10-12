<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Fee
 * 
 * @property int $id
 * @property int $student_id
 * @property string $fee_type
 * @property float $amount
 * @property float $paid_amount
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $payment_date
 * @property string $status
 * @property string|null $payment_method
 * @property string|null $transaction_id
 * @property string|null $invoice_number
 * @property string|null $remarks
 * @property int|null $collected_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read Student $student
 * @property-read User|null $collectedBy
 */
class Fee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'fee_type',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'paid_amount',
        'payment_method',
        'transaction_id',
        'invoice_number',
        'remarks',
        'collected_by',
        'late_fee_total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'late_fee_total' => 'decimal:2',
    ];

    /**
     * Get the student that owns the fee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who collected the fee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * New relationships for waivers and installments
     */
    public function waivers()
    {
        return $this->hasMany(FeeWaiver::class);
    }

    public function activeWaivers()
    {
        return $this->hasMany(FeeWaiver::class)->active();
    }

    public function installments()
    {
        return $this->hasMany(FeeInstallment::class)->orderBy('installment_number');
    }

    /**
     * Scope a query to only include paid fees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include unpaid fees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope a query to only include partial fees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    /**
     * Scope a query to only include fees for a specific student.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include fees for a specific class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $classId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClass($query, $classId)
    {
        return $query->whereHas('student', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        });
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('due_date', [$from, $to]);
    }

    /**
     * Scope a query to only include fees with a specific status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include fees with a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, $type)
    {
        return $query->where('fee_type', $type);
    }

    /**
     * Scope a query to only include overdue fees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['unpaid', 'partial']);
    }

    /**
     * Get the remaining amount to be paid taking waivers into account.
     *
     * @return float
     */
    public function getRemainingAmountAttribute()
    {
        $late = (float) ($this->late_fee_total ?? 0);
        $net = (float) $this->amount - (float) $this->total_waiver_amount + $late;
        return max(0, round($net - (float) $this->paid_amount, 2));
    }

    /**
     * Check if the fee is fully paid.
     *
     * @return bool
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->status === 'paid';
    }

    /**
     * Has installments accessor.
     */
    public function getHasInstallmentsAttribute(): bool
    {
        return $this->installments()->exists();
    }

    /**
     * Has active waivers accessor.
     */
    public function getHasActiveWaiversAttribute(): bool
    {
        return $this->activeWaivers()->exists();
    }

    /**
     * Sum of all active waiver amounts applied to this fee.
     */
    public function getTotalWaiverAmountAttribute(): float
    {
        $feeAmount = (float) $this->amount;

        $sum = $this->activeWaivers()
            ->get()
            ->filter(fn ($w) => $w->isValidFor($this->id))
            ->sum(function ($w) use ($feeAmount) {
                return $w->calculateWaiverAmount($feeAmount);
            });

        return round((float) $sum, 2);
    }

    /**
     * Net amount after waivers.
     */
    public function getNetAmountAttribute(): float
    {
        return max(0, round(
            (float)$this->amount - (float)$this->total_waiver_amount + (float) ($this->late_fee_total ?? 0),
            2
        ));
    }

    /**
     * Next unpaid installment (if any).
     */
    public function getNextInstallmentAttribute(): ?FeeInstallment
    {
        return $this->installments()->where('status', '!=', 'paid')->orderBy('installment_number')->first();
    }

    /**
     * Overdue installments collection.
     */
    public function getOverdueInstallmentsAttribute()
    {
        return $this->installments()->overdue()->get();
    }

    /**
     * Check if the fee is overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }

    /**
     * Mark the fee as paid (handles non-installment payments).
     *
     * @param float $amount
     * @param string $method
     * @param string|null $transactionId
     * @return void
     */
    public function markAsPaid($amount, $method, $transactionId = null)
    {
        $netAmount = (float) $this->net_amount;
        $newPaid = min($netAmount, (float)$amount);
        $this->paid_amount = $newPaid;
        $this->payment_method = $method;
        $this->transaction_id = $transactionId;
        $this->payment_date = now();
        $this->status = ($newPaid >= $netAmount) ? 'paid' : 'partial';
        $this->save();
    }

    /**
     * Check if the fee can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted()
    {
        return $this->status === 'unpaid';
    }

    /**
     * Get the payment progress percentage.
     *
     * @return float
     */
    public function getPaymentProgressPercentage()
    {
        $net = (float) $this->net_amount;
        if ($net == 0) {
            return 0;
        }

        return round(((float)$this->paid_amount / $net) * 100, 2);
    }

    /**
     * Apply a waiver to this fee by linking an existing waiver.
     */
    public function applyWaiver(int $waiverId): void
    {
        $waiver = $this->waivers()->where('id', $waiverId)->first();
        if (!$waiver) {
            // Link if the waiver belongs to the same student
            $w = FeeWaiver::query()->where('id', $waiverId)->where('student_id', $this->student_id)->first();
            if ($w) {
                $w->fee_id = $this->id;
                $w->save();
            }
        }
        $this->refresh();
    }

    /**
     * Create installment plan evenly across number of installments starting from start date.
     */
    public function createInstallmentPlan(int $numberOfInstallments, string $startDate): void
    {
        $this->installments()->delete();

        $net = (float) $this->net_amount;
        if ($numberOfInstallments <= 0 || $net <= 0) {
            return;
        }

        $base = floor(($net / $numberOfInstallments) * 100) / 100;
        $remainder = round($net - ($base * $numberOfInstallments), 2);

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $amount = $base + ($i === $numberOfInstallments ? $remainder : 0);
            $due = \Carbon\Carbon::parse($startDate)->addMonths($i - 1)->toDateString();

            $this->installments()->create([
                'installment_number' => $i,
                'amount' => $amount,
                'due_date' => $due,
                'status' => 'pending',
                'paid_amount' => 0,
            ]);
        }
    }
}

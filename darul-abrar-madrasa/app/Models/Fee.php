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
     * Get the remaining amount to be paid.
     *
     * @return float
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
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
     * Check if the fee is overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }

    /**
     * Mark the fee as paid.
     *
     * @param float $amount
     * @param string $method
     * @param string|null $transactionId
     * @return void
     */
    public function markAsPaid($amount, $method, $transactionId = null)
    {
        $this->paid_amount = $amount;
        $this->payment_method = $method;
        $this->transaction_id = $transactionId;
        $this->payment_date = now();
        $this->status = ($amount >= $this->amount) ? 'paid' : 'partial';
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
        if ($this->amount == 0) {
            return 0;
        }
        
        return round(($this->paid_amount / $this->amount) * 100, 2);
    }
}

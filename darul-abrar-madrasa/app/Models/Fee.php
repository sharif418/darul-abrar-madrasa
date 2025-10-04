<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who collected the fee.
     */
    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Get the remaining amount to be paid.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * Check if the fee is fully paid.
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the fee is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }

    /**
     * Scope a query to only include fees with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include fees with a specific type.
     */
    public function scopeType($query, $type)
    {
        return $query->where('fee_type', $type);
    }

    /**
     * Scope a query to only include overdue fees.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['unpaid', 'partial']);
    }
}
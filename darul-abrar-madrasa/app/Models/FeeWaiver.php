<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeWaiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_id',
        'waiver_type',
        'amount_type',
        'amount',
        'reason',
        'valid_from',
        'valid_until',
        'approved_by',
        'approved_at',
        'status',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->where('status', 'approved')
            ->where('valid_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
            });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeValidOn($query, $date)
    {
        return $query->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date);
            });
    }

    /**
     * Accessors
     */
    public function getIsActiveAttribute(): bool
    {
        $today = now()->toDateString();
        $withinDates = $this->valid_from->toDateString() <= $today
            && (is_null($this->valid_until) || $this->valid_until->toDateString() >= $today);

        return $this->status === 'approved' && $withinDates;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }

    /**
     * Helper / Business logic
     */
    public function approve(int $userId): void
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->rejection_reason = null;
        $this->save();
    }

    public function reject(int $userId, string $reason): void
    {
        $this->status = 'rejected';
        $this->approved_by = $userId;
        $this->approved_at = null;
        $this->rejection_reason = $reason;
        $this->save();
    }

    public function calculateWaiverAmount(float $feeAmount): float
    {
        if ($this->amount_type === 'percentage') {
            return round(($feeAmount * ((float) $this->amount)) / 100, 2);
        }
        return round((float) $this->amount, 2);
    }

    public function isValidFor(?int $feeId): bool
    {
        if ($this->fee_id === null) {
            return true;
        }
        return $this->fee_id === $feeId;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accountant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'designation',
        'qualification',
        'phone',
        'address',
        'joining_date',
        'salary',
        'can_approve_waivers',
        'can_approve_refunds',
        'max_waiver_amount',
        'is_active',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'can_approve_waivers' => 'boolean',
        'can_approve_refunds' => 'boolean',
        'max_waiver_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedWaivers()
    {
        return $this->hasMany(FeeWaiver::class, 'approved_by');
    }

    public function collectedFees()
    {
        return $this->hasMany(Fee::class, 'collected_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCanApproveWaivers($query)
    {
        return $query->where('can_approve_waivers', true);
    }

    public function scopeCanApproveRefunds($query)
    {
        return $query->where('can_approve_refunds', true);
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : '';
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user ? $this->user->email : null;
    }

    public function getYearsOfExperienceAttribute(): int
    {
        if (!$this->joining_date) {
            return 0;
        }
        return now()->diffInYears($this->joining_date);
    }

    /**
     * Helpers
     */
    public function canApproveWaiverAmount(float $amount): bool
    {
        if (!$this->can_approve_waivers) {
            return false;
        }
        if ($this->max_waiver_amount === null) {
            return true;
        }
        return $amount <= (float) $this->max_waiver_amount;
    }

    public function getTotalCollectedThisMonth(): float
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        return (float) $this->collectedFees()
            ->whereBetween('payment_date', [$start, $end])
            ->sum('paid_amount');
    }

    public function getApprovedWaiversCount(): int
    {
        return $this->approvedWaivers()->count();
    }
}

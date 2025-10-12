<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateFeePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee_type',
        'grace_period_days',
        'calculation_type',
        'amount',
        'max_late_fee',
        'compound',
        'exclude_holidays',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'grace_period_days' => 'integer',
        'amount' => 'decimal:2',
        'max_late_fee' => 'decimal:2',
        'compound' => 'boolean',
        'exclude_holidays' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForFeeType($query, ?string $feeType)
    {
        return $query->where(function ($q) use ($feeType) {
            $q->whereNull('fee_type')
              ->orWhere('fee_type', $feeType);
        });
    }

    /**
     * Static retriever with fallback to global policy
     */
    public static function getPolicyForFeeType(?string $feeType): ?self
    {
        // Prefer specific fee type over global using a single inclusive query for portability
        return self::query()
            ->active()
            ->forFeeType($feeType) // includes specific OR global
            ->orderByRaw('CASE WHEN fee_type IS NULL THEN 1 ELSE 0 END') // specific first, then global
            ->first();
    }

    /**
     * Helper to calculate late fee based on policy settings
     *
     * @param float $feeAmount Base fee/net amount to apply percentage on
     * @param int $daysOverdue Days overdue (already accounting for grace period if needed)
     * @return float Calculated late fee (capped to max_late_fee if provided)
     */
    public function calculateLateFee(float $feeAmount, int $daysOverdue): float
    {
        if ($daysOverdue <= 0) {
            return 0.0;
        }

        $amount = (float) $this->amount;
        $calculated = 0.0;

        switch ($this->calculation_type) {
            case 'fixed':
                $calculated = $amount;
                break;
            case 'percentage':
                $calculated = round(($feeAmount * $amount) / 100, 2);
                break;
            case 'daily':
                $calculated = round($amount * $daysOverdue, 2);
                break;
            case 'weekly':
                $weeks = (int) ceil($daysOverdue / 7);
                $calculated = round($amount * $weeks, 2);
                break;
            default:
                $calculated = 0.0;
        }

        if (!is_null($this->max_late_fee)) {
            $calculated = min($calculated, (float) $this->max_late_fee);
        }

        return max(0.0, $calculated);
    }

    /**
     * Whether policy applies considering grace period
     */
    public function isApplicable(\Carbon\CarbonInterface $dueDate): bool
    {
        $graceDays = (int) $this->grace_period_days;
        return now()->greaterThan($dueDate->copy()->addDays($graceDays));
    }
}

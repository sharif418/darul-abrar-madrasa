<?php

namespace App\Repositories;

use App\Models\Fee;
use App\Models\FeeWaiver;
use App\Models\FeeInstallment;
use App\Models\LateFeePolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\ActivityLogService;

class FeeRepository
{
    protected $fee;
    protected ?ActivityLogService $activity = null;

    public function __construct(Fee $fee, ActivityLogService $activity = null)
    {
        $this->fee = $fee;
        // Allow optional injection; fallback to container if not provided
        $this->activity = $activity ?: app(ActivityLogService::class);
    }

    /**
     * Get all fees with filters and pagination
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->fee->with(['student.user', 'student.class', 'collectedBy']);

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Fee type filter
        if (!empty($filters['fee_type'])) {
            $query->where('fee_type', $filters['fee_type']);
        }

        // Student filter
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->where('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('due_date', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get fee statistics
     */
    public function getStatistics($filters = [])
    {
        $query = $this->fee->query();

        // Apply filters if provided
        if (!empty($filters['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('due_date', '<=', $filters['date_to']);
        }

        $totalFees = $query->sum('amount');
        $collectedFees = $query->sum('paid_amount');
        $pendingFees = $totalFees - $collectedFees;

        $overdueFees = (clone $query)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

        $collectionRate = $totalFees > 0 ? ($collectedFees / $totalFees) * 100 : 0;

        // Additional stats
        $waiverTotals = FeeWaiver::query()->approved()->sum('amount');
        $installmentCount = FeeInstallment::query()->count();
        // Sum of late fees (fee-level late_fee_total plus installment-level late_fee_applied)
        $lateFeeTotals = (float) ($this->fee->sum('late_fee_total') + FeeInstallment::query()->sum('late_fee_applied'));

        return [
            'totalFees' => $totalFees,
            'collectedFees' => $collectedFees,
            'pendingFees' => $pendingFees,
            'overdueFees' => $overdueFees,
            'collectionRate' => round($collectionRate, 2),
            'paidCount' => (clone $query)->where('status', 'paid')->count(),
            'unpaidCount' => (clone $query)->where('status', 'unpaid')->count(),
            'partialCount' => (clone $query)->where('status', 'partial')->count(),
            'waiverTotals' => (float) $waiverTotals,
            'installmentCount' => (int) $installmentCount,
            'lateFeeTotals' => (float) $lateFeeTotals,
        ];
    }

    /**
     * Create a new fee
     */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $maxRetries = 5;
            $attempt = 0;

            while ($attempt < $maxRetries) {
                $attempt++;

                $feeData = [
                    'student_id' => $data['student_id'],
                    'fee_type' => $data['fee_type'],
                    'amount' => $data['amount'],
                    'due_date' => $data['due_date'],
                    'status' => $data['status'],
                    'paid_amount' => $data['paid_amount'] ?? 0,
                    'payment_method' => $data['payment_method'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                ];

                // Auto-generate invoice number if not provided
                if (empty($data['invoice_number'])) {
                    $feeData['invoice_number'] = $this->generateInvoiceNumber();
                    $feeData['invoice_generated_at'] = now();
                    $feeData['invoice_generated_by'] = Auth::id();
                } else {
                    $feeData['invoice_number'] = $data['invoice_number'];
                }

                // Set payment date and collected by if paid or partial
                if (in_array($data['status'], ['paid', 'partial'])) {
                    $feeData['payment_date'] = now();
                    $feeData['collected_by'] = Auth::id();
                }

                try {
                    return $this->fee->create($feeData);
                } catch (\Illuminate\Database\QueryException $e) {
                    // 23000 = integrity constraint violation (duplicate unique key, etc.)
                    // Retry only when we auto-generated the invoice number
                    if (!empty($data['invoice_number']) || $e->getCode() !== '23000') {
                        throw $e;
                    }
                    // Loop to retry with a newly generated invoice number
                    if ($attempt >= $maxRetries) {
                        throw new \RuntimeException('Failed to generate a unique invoice number after multiple attempts.', 0, $e);
                    }
                }
            }

            // Should never reach here
            throw new \RuntimeException('Unable to create fee record.');
        });
    }

    /**
     * Update a fee
     */
    public function update($fee, $data)
    {
        $feeData = [
            'student_id' => $data['student_id'],
            'fee_type' => $data['fee_type'],
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'status' => $data['status'],
            'paid_amount' => $data['paid_amount'] ?? 0,
            'payment_method' => $data['payment_method'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'remarks' => $data['remarks'] ?? null,
        ];

        // Update payment date and collected by if status changed to paid or partial
        if (in_array($data['status'], ['paid', 'partial']) && $fee->status === 'unpaid') {
            $feeData['payment_date'] = now();
            $feeData['collected_by'] = Auth::id();
        }

        $fee->update($feeData);

        return $fee->fresh(['student.user', 'student.class', 'collectedBy']);
    }

    /**
     * Record payment for a fee (handles installments if exist)
     */
    public function recordPayment($fee, $paymentData)
    {
        return DB::transaction(function () use ($fee, $paymentData) {
            $amountToApply = (float) ($paymentData['amount'] ?? 0);
            $method = $paymentData['payment_method'] ?? 'cash';
            $txn = $paymentData['transaction_id'] ?? null;
            $remarks = $paymentData['remarks'] ?? $fee->remarks;
            $prevStatus = $fee->status;

            if ($fee->installments()->exists()) {
                // Apply to installments sequentially
                $installments = $fee->installments()->orderBy('installment_number')->get();
                foreach ($installments as $inst) {
                    if ($amountToApply <= 0) {
                        break;
                    }
                    $remaining = (float) $inst->amount - (float) $inst->paid_amount;
                    if ($remaining <= 0) {
                        continue;
                    }
                    $payNow = min($remaining, $amountToApply);
                    $inst->markAsPaid($payNow, $method, $txn, Auth::id());
                    $amountToApply -= $payNow;
                }

                // Update fee aggregate
                $totalPaid = (float) $fee->installments()->sum('paid_amount');
                $feeNet = (float) $fee->net_amount;
                $fee->update([
                    'paid_amount' => min($feeNet, $totalPaid),
                    'status' => $totalPaid >= $feeNet ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
                    'payment_method' => $method,
                    'transaction_id' => $txn,
                    'payment_date' => now(),
                    'collected_by' => Auth::id(),
                    'remarks' => $remarks,
                ]);
            } else {
                // No installments - simple application
                $newPaidAmount = min((float)$fee->net_amount, (float)$fee->paid_amount + $amountToApply);
                $status = $newPaidAmount >= (float)$fee->net_amount ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid');

                $fee->update([
                    'paid_amount' => $newPaidAmount,
                    'status' => $status,
                    'payment_method' => $method,
                    'transaction_id' => $txn,
                    'payment_date' => now(),
                    'collected_by' => Auth::id(),
                    'remarks' => $remarks,
                ]);
            }

            $updated = $fee->fresh(['student.user', 'collectedBy', 'installments']);

            // Audit log
            if ($this->activity) {
                $this->activity->logFeePayment($updated, (float) ($paymentData['amount'] ?? 0), $method, [
                    'previous_status' => $prevStatus,
                    'new_status' => $updated->status,
                    'transaction_id' => $txn,
                ]);
            }

            return $updated;
        });
    }

    /**
     * Get collection report
     */
    public function getCollectionReport($filters, $perPage = 15)
    {
        $base = $this->fee->with(['student.user', 'student.class', 'collectedBy'])
            ->whereIn('status', ['paid', 'partial'])
            ->whereNotNull('payment_date');

        // Date range filter
        if (!empty($filters['date_from'])) {
            $base->where('payment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $base->where('payment_date', '<=', $filters['date_to']);
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $base->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        // Fee type filter
        if (!empty($filters['fee_type'])) {
            $base->where('fee_type', $filters['fee_type']);
        }

        // Paginated list of collections
        $fees = (clone $base)->latest('payment_date')->paginate($perPage);

        // Total collected amount
        $totalCollected = (clone $base)->sum('paid_amount');

        // Summary by fee type
        $summary = (clone $base)
            ->select('fee_type', DB::raw('COUNT(*) as total_transactions'), DB::raw('SUM(paid_amount) as total_collected'))
            ->groupBy('fee_type')
            ->get();

        return [
            'fees' => $fees,
            'summary' => $summary,
            'totalCollected' => $totalCollected,
        ];
    }

    /**
     * Get outstanding report
     */
    public function getOutstandingReport($filters, $perPage = 15)
    {
        $base = $this->fee->with(['student.user', 'student.class'])
            ->whereIn('status', ['unpaid', 'partial']);

        // Class filter
        if (!empty($filters['class_id'])) {
            $base->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        // Overdue filter
        if (!empty($filters['overdue'])) {
            $base->where('due_date', '<', now());
        }

        // Paginated outstanding fees
        $fees = (clone $base)->oldest('due_date')->paginate($perPage);

        // Total outstanding amount
        $totalOutstanding = (clone $base)->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

        // Summary by fee type
        $summary = (clone $base)
            ->select(
                'fee_type',
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(amount - COALESCE(paid_amount, 0)) as total_outstanding')
            )
            ->groupBy('fee_type')
            ->get();

        return [
            'fees' => $fees,
            'summary' => $summary,
            'totalOutstanding' => $totalOutstanding,
        ];
    }

    /**
     * Create bulk fees for multiple students
     */
    public function createBulk($studentIds, $feeData)
    {
        return DB::transaction(function () use ($studentIds, $feeData) {
            $count = 0;

            foreach ($studentIds as $studentId) {
                $this->fee->create([
                    'student_id' => $studentId,
                    'fee_type' => $feeData['fee_type'],
                    'amount' => $feeData['amount'],
                    'due_date' => $feeData['due_date'],
                    'status' => 'unpaid',
                    'paid_amount' => 0,
                    'remarks' => $feeData['remarks'] ?? null,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'invoice_generated_at' => now(),
                    'invoice_generated_by' => Auth::id(),
                ]);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Generate a unique invoice number in format INV-YYYY-MM-XXXXX using an atomic sequence.
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = env('INVOICE_PREFIX', 'INV');
        $period = now()->format('Y-m');

        return DB::transaction(function () use ($prefix, $period) {
            // Ensure a sequence row exists for this period in a driver-agnostic way (SQLite-safe)
            $exists = DB::table('invoice_sequences')->where('period', $period)->exists();
            if (!$exists) {
                DB::table('invoice_sequences')->insert([
                    'period' => $period,
                    'current' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Lock and increment atomically where supported (SQLite ignores lockForUpdate)
            $row = DB::table('invoice_sequences')->where('period', $period)->lockForUpdate()->first();
            $current = (int) ($row->current ?? 0);
            $next = $current + 1;

            DB::table('invoice_sequences')
                ->where('period', $period)
                ->update([
                    'current' => $next,
                    'updated_at' => now(),
                ]);

            return sprintf('%s-%s-%05d', $prefix, $period, $next);
        }, 3);
    }

    /**
     * Waiver management helpers
     */
    public function applyWaiverToFee(int $feeId, int $waiverId): Fee
    {
        return DB::transaction(function () use ($feeId, $waiverId) {
            $fee = $this->fee->findOrFail($feeId);
            $waiver = FeeWaiver::query()->findOrFail($waiverId);

            if ($waiver->student_id !== $fee->student_id) {
                throw new \InvalidArgumentException('Waiver does not belong to the same student.');
            }

            $waiver->fee_id = $fee->id;
            $waiver->save();

            return $fee->fresh(['waivers']);
        });
    }

    public function removeWaiverFromFee(int $feeId, int $waiverId): Fee
    {
        return DB::transaction(function () use ($feeId, $waiverId) {
            $fee = $this->fee->findOrFail($feeId);
            $waiver = FeeWaiver::query()->where('id', $waiverId)->where('fee_id', $feeId)->firstOrFail();
            $waiver->fee_id = null;
            $waiver->save();

            return $fee->fresh(['waivers']);
        });
    }

    public function getApplicableWaivers(int $studentId, ?string $feeType = null)
    {
        $today = now()->toDateString();
        return FeeWaiver::query()
            ->active()
            ->forStudent($studentId)
            ->when($feeType, function ($q) use ($feeType) {
                $q->where(function ($inner) use ($feeType) {
                    $inner->whereNull('fee_id'); // general waivers
                });
            })
            ->get();
    }

    public function calculateWaiverAmount(Fee $fee, FeeWaiver $waiver): float
    {
        return $waiver->calculateWaiverAmount((float) $fee->amount);
    }

    /**
     * Installment helpers
     */
    public function createInstallmentPlan(int $feeId, int $numberOfInstallments, string $startDate, string $frequency = 'monthly'): Fee
    {
        return DB::transaction(function () use ($feeId, $numberOfInstallments, $startDate, $frequency) {
            $fee = $this->fee->findOrFail($feeId);
            $fee->createInstallmentPlan($numberOfInstallments, $startDate);
            return $fee->fresh(['installments']);
        });
    }

    public function recordInstallmentPayment(int $installmentId, float $amount, string $paymentMethod, ?string $transactionId = null): FeeInstallment
    {
        return DB::transaction(function () use ($installmentId, $amount, $paymentMethod, $transactionId) {
            $inst = FeeInstallment::query()->findOrFail($installmentId);
            if (!$inst->canBePaid()) {
                throw new \RuntimeException('Previous installment must be paid first.');
            }
            $inst->markAsPaid($amount, $paymentMethod, $transactionId, Auth::id());

            // Update fee aggregate
            $fee = $inst->fee()->first();
            $totalPaid = (float) $fee->installments()->sum('paid_amount');
            $feeNet = (float) $fee->net_amount;
            $fee->update([
                'paid_amount' => min($feeNet, $totalPaid),
                'status' => $totalPaid >= $feeNet ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'payment_date' => now(),
                'collected_by' => Auth::id(),
            ]);

            return $inst->fresh();
        });
    }

    public function getOverdueInstallments(int $studentId)
    {
        return FeeInstallment::query()
            ->whereHas('fee', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->overdue()
            ->get();
    }

    public function applyLateFeeToOverdueInstallments(): int
    {
        $count = 0;
        $overdues = FeeInstallment::query()->overdue()->with('fee')->get();
        foreach ($overdues as $inst) {
            $policy = $this->getLateFeePolicy($inst->fee->fee_type);
            if (!$policy) {
                continue;
            }
            $rawDaysOverdue = max(0, now()->diffInDays($inst->due_date));
            $grace = (int) ($policy->grace_period_days ?? 0);
            $daysOverdue = max(0, $rawDaysOverdue - $grace);
            $feeAmount = (float) $inst->amount;
            $late = $policy->calculateLateFee($feeAmount, $daysOverdue);
            if ($late > 0) {
                $inst->applyLateFee($late);
                // Audit log at fee level for installment late fee applications
                if ($this->activity) {
                    $this->activity->logLateFeeApplication($inst->fee, $late, [
                        'days_overdue' => $daysOverdue,
                        'policy_used' => $policy->name ?? null,
                        'installment_id' => $inst->id,
                        'installment_number' => $inst->installment_number,
                    ]);
                }
                $count++;
            }
        }
        return $count;
    }

    /**
     * Late fee processors
     */
    public function calculateAndApplyLateFees(int $feeId): Fee
    {
        return DB::transaction(function () use ($feeId) {
            $fee = $this->fee->findOrFail($feeId);

            // Retrieve applicable policy (specific or global)
            $policy = $this->getLateFeePolicy($fee->fee_type);
            if (!$policy) {
                return $fee;
            }

            // Compute days overdue (SQLite-safe and explicit)
            $rawDaysOverdue = \Carbon\Carbon::parse($fee->due_date)->diffInDays(now());
            $grace = (int) ($policy->grace_period_days ?? 0);
            $daysOverdue = max(0, $rawDaysOverdue - $grace);

            // If still within grace, nothing to do
            if ($daysOverdue <= 0) {
                return $fee->fresh(['installments']);
            }

            $late = $policy->calculateLateFee((float) $fee->net_amount, $daysOverdue);

            if ($late > 0 && !$fee->installments()->exists()) {
                // Track late fee separately without mutating principal amount
                $fee->late_fee_total = (float) ($fee->late_fee_total ?? 0) + (float) $late;
                $fee->save();

                if ($this->activity) {
                    $this->activity->logLateFeeApplication($fee, $late, [
                        'days_overdue' => $daysOverdue,
                        'policy_used' => $policy->name ?? null,
                    ]);
                }
            } elseif ($late > 0 && $fee->installments()->exists()) {
                // Apply to next unpaid installment
                $next = $fee->next_installment;
                if ($next) {
                    $next->applyLateFee($late);
                    if ($this->activity) {
                        $this->activity->logLateFeeApplication($fee, $late, [
                            'days_overdue' => $daysOverdue,
                            'policy_used' => $policy->name ?? null,
                            'installment_id' => $next->id,
                            'installment_number' => $next->installment_number,
                        ]);
                    }
                }
            }

            return $fee->fresh(['installments']);
        });
    }

    public function getLateFeePolicy(?string $feeType): ?LateFeePolicy
    {
        // Try specific/global policy first, then fall back to any active policy
        $policy = LateFeePolicy::getPolicyForFeeType($feeType);
        if (!$policy) {
            $policy = LateFeePolicy::query()->active()->orderBy('id', 'desc')->first();
        }
        return $policy;
    }

    public function processOverdueFees(): int
    {
        $count = 0;
        $fees = $this->fee->overdue()->get();
        foreach ($fees as $fee) {
            $updated = $this->calculateAndApplyLateFees($fee->id);
            if ($updated) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Reporting methods
     */
    public function getWaiverReport($filters = [])
    {
        $base = FeeWaiver::query()->with(['student.user', 'fee']);
        if (!empty($filters['status'])) {
            $base->where('status', $filters['status']);
        }
        if (!empty($filters['student_id'])) {
            $base->where('student_id', $filters['student_id']);
        }
        if (!empty($filters['date_from'])) {
            $base->where('valid_from', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $base->where(function ($q) use ($filters) {
                $q->whereNull('valid_until')->orWhere('valid_until', '<=', $filters['date_to']);
            });
        }

        $summaryByType = (clone $base)
            ->select('waiver_type', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('waiver_type')
            ->get();

        $list = $base->latest()->paginate($filters['per_page'] ?? 15);

        return [
            'list' => $list,
            'summaryByType' => $summaryByType,
            'total' => (clone $base)->count(),
        ];
    }

    public function getInstallmentReport($filters = [])
    {
        $base = FeeInstallment::query()->with(['fee.student.user']);
        if (!empty($filters['status'])) {
            $base->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $base->where('due_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $base->where('due_date', '<=', $filters['date_to']);
        }

        $list = $base->orderBy('due_date')->paginate($filters['per_page'] ?? 15);

        $summary = [
            'totalInstallments' => (clone $base)->count(),
            'paidInstallments' => (clone $base)->where('status', 'paid')->count(),
            'overdueInstallments' => (clone $base)->overdue()->count(),
            'totalAmount' => (clone $base)->sum('amount'),
            'totalPaid' => (clone $base)->sum('paid_amount'),
        ];

        return [
            'list' => $list,
            'summary' => $summary,
        ];
    }

    public function getLateFeeReport($filters = [])
    {
        // If late fees are applied as part of fee/instalment amounts, we can approximate with overdue stats.
        $overdueFees = $this->fee->overdue()->with('student.user')->paginate($filters['per_page'] ?? 15);

        return [
            'fees' => $overdueFees,
            'count' => $overdueFees->total(),
        ];
    }
}

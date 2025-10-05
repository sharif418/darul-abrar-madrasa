<?php

namespace App\Repositories;

use App\Models\Fee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FeeRepository
{
    protected $fee;

    public function __construct(Fee $fee)
    {
        $this->fee = $fee;
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

        return [
            'totalFees' => $totalFees,
            'collectedFees' => $collectedFees,
            'pendingFees' => $pendingFees,
            'overdueFees' => $overdueFees,
            'collectionRate' => round($collectionRate, 2),
            'paidCount' => (clone $query)->where('status', 'paid')->count(),
            'unpaidCount' => (clone $query)->where('status', 'unpaid')->count(),
            'partialCount' => (clone $query)->where('status', 'partial')->count(),
        ];
    }

    /**
     * Create a new fee
     */
    public function create($data)
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

        // Set payment date and collected by if paid or partial
        if (in_array($data['status'], ['paid', 'partial'])) {
            $feeData['payment_date'] = now();
            $feeData['collected_by'] = Auth::id();
        }

        return $this->fee->create($feeData);
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
     * Record payment for a fee
     */
    public function recordPayment($fee, $paymentData)
    {
        $newPaidAmount = $fee->paid_amount + $paymentData['amount'];
        
        // Determine new status
        if ($newPaidAmount >= $fee->amount) {
            $status = 'paid';
            $newPaidAmount = $fee->amount; // Cap at total amount
        } elseif ($newPaidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        $fee->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status,
            'payment_method' => $paymentData['payment_method'],
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'payment_date' => now(),
            'collected_by' => Auth::id(),
            'remarks' => $paymentData['remarks'] ?? $fee->remarks,
        ]);

        return $fee->fresh(['student.user', 'collectedBy']);
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
                ]);
                $count++;
            }

            return $count;
        });
    }
}

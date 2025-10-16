<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ActivityLogService
{
    /**
     * Log a general activity with optional polymorphic subject and properties.
     *
     * @param string $logName
     * @param string $description
     * @param mixed|null $subject Eloquent model or ['type' => class, 'id' => id]
     * @param array $properties
     * @param string|null $event
     * @param string|null $batchUuid
     * @return int Inserted log ID
     */
    public function log(
        string $logName,
        string $description,
        $subject = null,
        array $properties = [],
        ?string $event = null,
        ?string $batchUuid = null
    ): int {
        $now = Carbon::now();
        $causer = Auth::user();
        $causerType = $causer ? get_class($causer) : null;
        $causerId = $causer->id ?? null;

        $subjectType = null;
        $subjectId = null;

        if ($subject) {
            if (is_object($subject) && method_exists($subject, 'getKey')) {
                $subjectType = get_class($subject);
                $subjectId = $subject->getKey();
            } elseif (is_array($subject)) {
                $subjectType = $subject['type'] ?? null;
                $subjectId = $subject['id'] ?? null;
            }
        }

        $request = request();
        $ipAddress = $request ? $request->ip() : null;
        $userAgent = $request ? $request->userAgent() : null;

        $insertId = DB::table('activity_logs')->insertGetId([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'causer_type' => $causerType,
            'causer_id' => $causerId,
            'properties' => !empty($properties) ? json_encode($properties) : null,
            'event' => $event,
            'batch_uuid' => $batchUuid,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $insertId;
    }

    /**
     * Log a fee payment action.
     *
     * @param mixed $fee Eloquent Fee model
     * @param float|int $amount
     * @param string $paymentMethod
     * @param array $extra
     * @return int
     */
    public function logFeePayment($fee, $amount, string $paymentMethod, array $extra = []): int
    {
        $properties = array_merge([
            'amount' => (float) $amount,
            'payment_method' => $paymentMethod,
            'fee_id' => $fee->id ?? null,
            'student_id' => $fee->student_id ?? null,
            'previous_status' => $extra['previous_status'] ?? null,
            'new_status' => $extra['new_status'] ?? null,
            'transaction_id' => $extra['transaction_id'] ?? null,
        ], $extra);

        return $this->log(
            'fees',
            'Fee payment recorded',
            $fee,
            $properties,
            'updated'
        );
    }

    /**
     * Log a waiver approval action.
     *
     * @param mixed $waiver Eloquent FeeWaiver model
     * @param mixed $approver Eloquent User model
     * @return int
     */
    public function logWaiverApproval($waiver, $approver): int
    {
        $properties = [
            'waiver_id' => $waiver->id ?? null,
            'student_id' => $waiver->student_id ?? null,
            'fee_id' => $waiver->fee_id ?? null,
            'waiver_amount' => (float) ($waiver->amount ?? 0),
            'amount_type' => $waiver->amount_type ?? null,
            'approved_by' => $approver->id ?? null,
            'approval_date' => Carbon::now()->toDateTimeString(),
        ];

        return $this->log(
            'waivers',
            'Waiver approved',
            $waiver,
            $properties,
            'approved'
        );
    }

    /**
     * Log a waiver rejection action.
     *
     * @param mixed $waiver
     * @param mixed $approver
     * @param string $reason
     * @return int
     */
    public function logWaiverRejection($waiver, $approver, string $reason): int
    {
        $properties = [
            'waiver_id' => $waiver->id ?? null,
            'student_id' => $waiver->student_id ?? null,
            'fee_id' => $waiver->fee_id ?? null,
            'rejected_by' => $approver->id ?? null,
            'rejection_reason' => $reason,
            'rejected_at' => Carbon::now()->toDateTimeString(),
        ];

        return $this->log(
            'waivers',
            'Waiver rejected',
            $waiver,
            $properties,
            'rejected'
        );
    }

    /**
     * Log installment plan creation.
     *
     * @param mixed $fee
     * @param int $numberOfInstallments
     * @param array $details optional schedule details
     * @return int
     */
    public function logInstallmentCreation($fee, int $numberOfInstallments, array $details = []): int
    {
        $properties = [
            'fee_id' => $fee->id ?? null,
            'student_id' => $fee->student_id ?? null,
            'number_of_installments' => $numberOfInstallments,
            'installment_details' => $details,
        ];

        return $this->log(
            'installments',
            'Installment plan created',
            $fee,
            $properties,
            'created'
        );
    }

    /**
     * Log late fee application.
     *
     * @param mixed $fee
     * @param float|int $lateFeeAmount
     * @param array $context
     * @return int
     */
    public function logLateFeeApplication($fee, $lateFeeAmount, array $context = []): int
    {
        $properties = array_merge([
            'fee_id' => $fee->id ?? null,
            'student_id' => $fee->student_id ?? null,
            'late_fee_amount' => (float) $lateFeeAmount,
            'days_overdue' => $context['days_overdue'] ?? null,
            'policy_used' => $context['policy_used'] ?? null,
        ], $context);

        return $this->log(
            'late_fees',
            'Late fee applied',
            $fee,
            $properties,
            'updated'
        );
    }

    /**
     * Retrieve activity logs with filters.
     *
     * @param array $filters
     *   log_name, causer_id, subject_type, subject_id, date_from, date_to, event
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getActivityLogs(array $filters = [], int $perPage = 20)
    {
        $q = DB::table('activity_logs')->orderByDesc('created_at');

        if (!empty($filters['log_name'])) {
            $q->where('log_name', $filters['log_name']);
        }
        if (!empty($filters['causer_id'])) {
            $q->where('causer_id', $filters['causer_id']);
        }
        if (!empty($filters['subject_type'])) {
            $q->where('subject_type', $filters['subject_type']);
        }
        if (!empty($filters['subject_id'])) {
            $q->where('subject_id', $filters['subject_id']);
        }
        if (!empty($filters['event'])) {
            $q->where('event', $filters['event']);
        }
        if (!empty($filters['date_from'])) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $q->paginate($perPage);
    }

    /**
     * Get recent activity for a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserActivity(int $userId, int $limit = 50)
    {
        return DB::table('activity_logs')
            ->where('causer_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity for a specific subject model.
     *
     * @param string $subjectType
     * @param int $subjectId
     * @return \Illuminate\Support\Collection
     */
    public function getSubjectActivity(string $subjectType, int $subjectId)
    {
        return DB::table('activity_logs')
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderByDesc('created_at')
            ->get();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Models\Notification;

class SendFeeReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *  --dry-run            Show what would be sent without delivering notifications
     *  --overdue-only       Send only overdue reminders (exclude upcoming)
     *  --days=              Window in days for upcoming dues (default 7)
     */
    protected $signature = 'fees:send-reminders
                            {--dry-run : Show messages without sending}
                            {--overdue-only : Only send overdue reminders}
                            {--days=7 : Days ahead for upcoming due reminders}';

    /**
     * The console command description.
     */
    protected $description = 'Send fee payment reminders to guardians responsible for payments';

    public function handle(NotificationService $notificationService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $overdueOnly = (bool) $this->option('overdue-only');
        $days = (int) $this->option('days');

        $today = Carbon::today();
        $upTo = $today->copy()->addDays(max(0, $days));

        $this->info("Preparing reminders. Overdue-only: ".($overdueOnly ? 'yes' : 'no').", window: {$days} days".($dryRun ? ' [DRY RUN]' : ''));

        try {
            // Load fees and link to guardians with financial responsibility via pivot guardian_student
            $feesQuery = DB::table('fees')
                ->select([
                    'fees.id as fee_id', 'fees.student_id', 'fees.amount', 'fees.paid_amount', 'fees.status',
                    'fees.due_date', 'fees.fee_type',
                    'students.class_id',
                    'guardian_student.guardian_id',
                    'guardian_student.financial_responsibility',
                    'guardians.phone', 'guardians.email',
                    'users.name as guardian_name',
                ])
                ->join('students', 'students.id', '=', 'fees.student_id')
                ->join('guardian_student', 'guardian_student.student_id', '=', 'students.id')
                ->join('guardians', 'guardians.id', '=', 'guardian_student.guardian_id')
                ->join('users', 'users.id', '=', 'guardians.user_id')
                ->whereIn('fees.status', ['unpaid', 'partial'])
                ->where('guardian_student.financial_responsibility', '=', 1);

            if ($overdueOnly) {
                $feesQuery->whereDate('fees.due_date', '<', $today);
            } else {
                $feesQuery->where(function ($q) use ($today, $upTo) {
                    $q->whereDate('fees.due_date', '<', $today)
                      ->orWhereBetween('fees.due_date', [$today, $upTo]);
                });
            }

            $fees = $feesQuery->orderBy('guardian_student.guardian_id')->orderBy('fees.due_date')->get();

            if ($fees->isEmpty()) {
                $this->info('No fees matched the reminder criteria.');
                return self::SUCCESS;
            }

            // Group by guardian -> student -> fees
            $byGuardian = [];
            foreach ($fees as $row) {
                $gid = (int) $row->guardian_id;
                $sid = (int) $row->student_id;
                $byGuardian[$gid] = $byGuardian[$gid] ?? [
                    'guardian' => [
                        'id' => $gid,
                        'name' => $row->guardian_name ?? 'Guardian',
                        'phone' => $row->phone ?? null,
                        'email' => $row->email ?? null,
                    ],
                    'students' => [],
                ];
                $byGuardian[$gid]['students'][$sid] = $byGuardian[$gid]['students'][$sid] ?? [
                    'student_id' => $sid,
                    'fees' => [],
                ];
                $byGuardian[$gid]['students'][$sid]['fees'][] = [
                    'fee_id' => $row->fee_id,
                    'fee_type' => $row->fee_type,
                    'amount' => (float) $row->amount,
                    'paid_amount' => (float) ($row->paid_amount ?? 0),
                    'status' => $row->status,
                    'due_date' => $row->due_date,
                ];
            }

            $reminderCount = 0;
            $totalPending = 0.0;

            foreach ($byGuardian as $gid => $bundle) {
                $guardian = $bundle['guardian'];
                $message = $this->buildMessage($guardian, $bundle['students'], $today, $overdueOnly);

                $guardianPending = $message['total_pending'];
                $totalPending += $guardianPending;

                if ($dryRun) {
                    $this->line('---');
                    $this->line(sprintf(
                        '[DRY] Guardian #%d %s | Phone: %s | Email: %s | Pending: ৳ %s',
                        $gid,
                        $guardian['name'],
                        $guardian['phone'] ?? '-',
                        $guardian['email'] ?? '-',
                        number_format($guardianPending, 2)
                    ));
                    $this->line($message['text']);
                    $reminderCount++;
                    continue;
                }

                // Send notification via NotificationService
                $notificationData = [
                    'guardian_name' => $guardian['name'],
                    'message' => $message['text'],
                    'total_pending' => number_format($guardianPending, 2),
                    'due_amount' => number_format($guardianPending, 2),
                    'students' => array_map(function ($student) {
                        return [
                            'student_id' => $student['student_id'],
                            'fees_count' => count($student['fees']),
                        ];
                    }, $bundle['students']),
                ];

                $notificationId = $notificationService->sendNotification(
                    Notification::TYPE_FEE_DUE,
                    $guardian['id'],
                    'guardian',
                    $notificationData
                );

                if ($notificationId) {
                    $this->line(sprintf(
                        'Notification sent to Guardian #%d %s | Pending: ৳ %s',
                        $gid,
                        $guardian['name'],
                        number_format($guardianPending, 2)
                    ));
                    $reminderCount++;
                } else {
                    $this->error(sprintf(
                        'Failed to send notification to Guardian #%d %s',
                        $gid,
                        $guardian['name']
                    ));
                }

                Log::info('Fee reminder sent', [
                    'guardian_id' => $gid,
                    'notification_id' => $notificationId,
                    'pending' => $guardianPending,
                    'overdue_only' => $overdueOnly,
                    'window_days' => $days,
                ]);
            }

            $summary = sprintf(
                'Prepared %d reminders. Aggregate pending: ৳ %s%s',
                $reminderCount,
                number_format($totalPending, 2),
                $dryRun ? ' [DRY RUN]' : ''
            );
            $dryRun ? $this->comment($summary) : $this->info($summary);
            Log::info('SendFeeReminders summary', [
                'reminders' => $reminderCount,
                'total_pending' => $totalPending,
                'dry_run' => $dryRun,
                'overdue_only' => $overdueOnly,
                'days' => $days,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error sending reminders: '.$e->getMessage());
            Log::error('SendFeeReminders failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }

    /**
     * Build a consolidated reminder message for a guardian.
     *
     * @param array $guardian
     * @param array $students
     * @param \Illuminate\Support\Carbon $today
     * @param bool $overdueOnly
     * @return array{ text: string, total_pending: float }
     */
    protected function buildMessage(array $guardian, array $students, Carbon $today, bool $overdueOnly): array
    {
        $lines = [];
        $lines[] = "Assalamu alaikum {$guardian['name']},";
        $lines[] = $overdueOnly
            ? "This is a reminder for your child's overdue fees:"
            : "This is a reminder for your child's upcoming/overdue fees:";

        $totalPending = 0.0;
        foreach ($students as $sid => $bundle) {
            $feesLines = [];
            $studentTotal = 0.0;

            foreach ($bundle['fees'] as $f) {
                $remaining = max(0, (float)$f['amount'] - (float)$f['paid_amount']);
                if ($remaining <= 0) {
                    continue;
                }
                $studentTotal += $remaining;
                $dueDate = $f['due_date'] ? Carbon::parse($f['due_date'])->format('d M Y') : '-';

                $feesLines[] = sprintf(
                    '• %s fee: ৳ %s (due %s)',
                    ucfirst($f['fee_type'] ?? 'fee'),
                    number_format($remaining, 2),
                    $dueDate
                );
            }

            if (!empty($feesLines)) {
                $lines[] = "Student ID #{$sid}:";
                $lines = array_merge($lines, $feesLines);
                $lines[] = sprintf('Subtotal: ৳ %s', number_format($studentTotal, 2));
                $totalPending += $studentTotal;
            }
        }

        if ($totalPending <= 0) {
            $lines[] = 'No pending items found.';
        } else {
            $lines[] = sprintf('Total Pending: ৳ %s', number_format($totalPending, 2));
        }

        $lines[] = 'Please pay at your earliest convenience. JazakAllah khair.';
        $text = implode("\n", $lines);

        return [
            'text' => $text,
            'total_pending' => $totalPending,
        ];
    }
}

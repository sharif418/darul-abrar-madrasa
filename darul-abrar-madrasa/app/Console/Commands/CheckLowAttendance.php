<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Repositories\AttendanceRepository;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class CheckLowAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-low 
                            {--threshold=75 : Attendance percentage threshold}
                            {--days=30 : Days to check}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for students with low attendance and notify guardians';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService, AttendanceRepository $attendanceRepository): int
    {
        $threshold = (float) $this->option('threshold');
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for students with attendance below {$threshold}% in the last {$days} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $studentsChecked = 0;
        $notificationsSent = 0;
        $students = Student::with(['class', 'guardians'])->get();

        foreach ($students as $student) {
            $studentsChecked++;

            // Calculate attendance rate
            $attendanceRate = $attendanceRepository->getAttendanceRate($student->id, $days);

            if ($attendanceRate < $threshold) {
                $absentDays = $attendanceRepository->getAbsentDays($student->id, $days);
                
                $this->line("Low attendance detected: {$student->name} ({$attendanceRate}%)");

                // Get guardians with notifications enabled
                $guardians = $student->guardians()
                    ->wherePivot('receive_notifications', true)
                    ->get();

                foreach ($guardians as $guardian) {
                    $data = [
                        'student_name' => $student->name,
                        'guardian_name' => $guardian->name,
                        'attendance_rate' => number_format($attendanceRate, 1),
                        'threshold' => $threshold,
                        'absent_days' => $absentDays,
                        'period_days' => $days,
                        'class_name' => $student->class->name ?? 'N/A',
                    ];

                    if (!$dryRun) {
                        $notificationId = $notificationService->sendNotification(
                            Notification::TYPE_LOW_ATTENDANCE,
                            $guardian->id,
                            'guardian',
                            $data
                        );

                        if ($notificationId) {
                            $notificationsSent++;
                            $this->info("  → Notification sent to {$guardian->name}");
                        } else {
                            $this->error("  → Failed to send notification to {$guardian->name}");
                        }
                    } else {
                        $this->comment("  → Would send notification to {$guardian->name}");
                        $notificationsSent++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Students checked: {$studentsChecked}");
        $this->info("  Notifications " . ($dryRun ? 'would be' : '') . " sent: {$notificationsSent}");

        Log::info('Low attendance check completed', [
            'students_checked' => $studentsChecked,
            'notifications_sent' => $notificationsSent,
            'threshold' => $threshold,
            'days' => $days,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendExamScheduleNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:notify-schedule 
                            {--days=7 : Days ahead to notify}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send exam schedule notifications to guardians';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $daysAhead = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for exams starting within the next {$daysAhead} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $examsChecked = 0;
        $notificationsSent = 0;

        // Get upcoming exams
        $upcomingExams = Exam::with(['class', 'subjects'])
            ->where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays($daysAhead))
            ->get();

        foreach ($upcomingExams as $exam) {
            $examsChecked++;
            
            $this->line("Upcoming exam: {$exam->name} for {$exam->class->name} on {$exam->start_date->format('Y-m-d')}");

            // Get all students in the exam's class
            $students = Student::where('class_id', $exam->class_id)
                ->with('guardians')
                ->get();

            foreach ($students as $student) {
                // Get guardians with notifications enabled
                $guardians = $student->guardians()
                    ->wherePivot('receive_notifications', true)
                    ->get();

                foreach ($guardians as $guardian) {
                    $subjects = $exam->subjects->pluck('name')->implode(', ');
                    
                    $data = [
                        'student_name' => $student->name,
                        'guardian_name' => $guardian->name,
                        'exam_name' => $exam->name,
                        'start_date' => $exam->start_date->format('l, F j, Y'),
                        'end_date' => $exam->end_date->format('l, F j, Y'),
                        'class_name' => $exam->class->name,
                        'subjects' => $subjects ?: 'All subjects',
                        'days_until' => now()->diffInDays($exam->start_date),
                    ];

                    if (!$dryRun) {
                        $notificationId = $notificationService->sendNotification(
                            Notification::TYPE_EXAM_SCHEDULE,
                            $guardian->id,
                            'guardian',
                            $data
                        );

                        if ($notificationId) {
                            $notificationsSent++;
                            $this->info("  → Notification sent to {$guardian->name} for {$student->name}");
                        } else {
                            $this->error("  → Failed to send notification to {$guardian->name}");
                        }
                    } else {
                        $this->comment("  → Would send notification to {$guardian->name} for {$student->name}");
                        $notificationsSent++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Exams checked: {$examsChecked}");
        $this->info("  Notifications " . ($dryRun ? 'would be' : '') . " sent: {$notificationsSent}");

        Log::info('Exam schedule notifications completed', [
            'exams_checked' => $examsChecked,
            'notifications_sent' => $notificationsSent,
            'days_ahead' => $daysAhead,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }
}

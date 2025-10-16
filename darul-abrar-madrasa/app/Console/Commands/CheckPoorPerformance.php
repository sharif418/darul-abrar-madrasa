<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\Student;
use App\Models\Result;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckPoorPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:check-poor 
                            {--threshold=2.5 : GPA threshold}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for students with poor performance and notify guardians';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $threshold = (float) $this->option('threshold');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for students with GPA below {$threshold}...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $studentsChecked = 0;
        $notificationsSent = 0;

        // Get students with recent published exam results
        $students = Student::with(['class', 'guardians', 'results.exam'])
            ->whereHas('results.exam', function ($query) {
                $query->where('is_result_published', true);
            })
            ->get();

        foreach ($students as $student) {
            $studentsChecked++;

            // Calculate average GPA from recent published results
            $avgGpa = $student->results()
                ->whereHas('exam', function ($query) {
                    $query->where('is_result_published', true)
                        ->where('end_date', '>=', now()->subMonths(3));
                })
                ->avg('gpa');

            if ($avgGpa && $avgGpa < $threshold) {
                // Get weak subjects
                $weakSubjects = $student->results()
                    ->whereHas('exam', function ($query) {
                        $query->where('is_result_published', true)
                            ->where('end_date', '>=', now()->subMonths(3));
                    })
                    ->where('gpa', '<', $threshold)
                    ->with('subject')
                    ->get()
                    ->pluck('subject.name')
                    ->unique()
                    ->take(5)
                    ->implode(', ');

                $this->line("Poor performance detected: {$student->name} (GPA: " . number_format($avgGpa, 2) . ")");

                // Get guardians with notifications enabled
                $guardians = $student->guardians()
                    ->wherePivot('receive_notifications', true)
                    ->get();

                foreach ($guardians as $guardian) {
                    $data = [
                        'student_name' => $student->name,
                        'guardian_name' => $guardian->name,
                        'gpa' => number_format($avgGpa, 2),
                        'threshold' => $threshold,
                        'weak_subjects' => $weakSubjects ?: 'N/A',
                        'class_name' => $student->class->name ?? 'N/A',
                        'recommendations' => 'Please schedule a meeting with the class teacher to discuss improvement strategies.',
                    ];

                    if (!$dryRun) {
                        $notificationId = $notificationService->sendNotification(
                            Notification::TYPE_POOR_PERFORMANCE,
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

        Log::info('Poor performance check completed', [
            'students_checked' => $studentsChecked,
            'notifications_sent' => $notificationsSent,
            'threshold' => $threshold,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }
}

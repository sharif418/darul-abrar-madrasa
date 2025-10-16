<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Fee;
use App\Models\Result;
use App\Models\Exam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifySystemIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:system-integrity 
                            {--fix : Automatically fix issues that are safe to repair}
                            {--role= : Check only specific role (teacher, student, guardian, accountant)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive system integrity verification with optional auto-repair';

    private array $issues = [];
    private array $fixed = [];
    private int $totalIssues = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('  System Integrity Verification');
        $this->info('===========================================');
        $this->newLine();

        $fix = $this->option('fix');
        $specificRole = $this->option('role');

        if ($fix) {
            $this->warn('🔧 AUTO-FIX MODE ENABLED');
            $this->newLine();
        }

        // Run all checks
        $this->checkUserRoleConsistency($specificRole, $fix);
        $this->checkRoleRecordsExistence($specificRole, $fix);
        $this->checkOrphanedRecords($fix);
        $this->checkDataIntegrity($fix);

        // Display summary
        $this->displaySummary($fix);

        // Generate log file
        $this->generateLogFile();

        return $this->totalIssues === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Check user-role consistency between legacy and Spatie.
     */
    private function checkUserRoleConsistency(?string $specificRole, bool $fix): void
    {
        $this->info('📋 Check 1: User-Role Consistency');
        $this->line('Checking if legacy role column matches Spatie roles...');
        $this->newLine();

        $query = User::with('roles');
        if ($specificRole) {
            $query->where('role', $specificRole);
        }

        $users = $query->get();
        $mismatches = 0;

        foreach ($users as $user) {
            $legacyRole = $user->role;
            $spatieRoles = $user->roles->pluck('name')->toArray();

            // Comment 6: Check ALL roles including admin/staff (removed special-case exclusion)
            if (!in_array($legacyRole, $spatieRoles)) {
                $mismatches++;
                $this->issues[] = [
                    'type' => 'Role Mismatch',
                    'user' => $user->email,
                    'legacy_role' => $legacyRole,
                    'spatie_roles' => implode(', ', $spatieRoles),
                ];
                $this->warn("  ❌ {$user->email}: Legacy={$legacyRole}, Spatie=" . implode(',', $spatieRoles));

                if ($fix) {
                    try {
                        $user->syncRoles([$legacyRole]);
                        $this->fixed[] = "Synced role for {$user->email}";
                        $this->info("    ✅ Fixed: Assigned Spatie role '{$legacyRole}'");
                    } catch (\Throwable $e) {
                        $this->error("    ❌ Fix failed: " . $e->getMessage());
                    }
                }
            }
        }

        $this->totalIssues += $mismatches;
        
        if ($mismatches === 0) {
            $this->info('  ✅ All user roles are consistent');
        } else {
            $this->warn("  Found {$mismatches} mismatches");
        }
        $this->newLine();
    }

    /**
     * Check if role records exist for all users.
     */
    private function checkRoleRecordsExistence(?string $specificRole, bool $fix): void
    {
        $this->info('📋 Check 2: Role Records Existence');
        $this->line('Checking if users have corresponding role records...');
        $this->newLine();

        $rolesToCheck = $specificRole ? [$specificRole] : ['teacher', 'student', 'guardian', 'accountant'];
        $totalMissing = 0;

        foreach ($rolesToCheck as $role) {
            $users = User::where('role', $role)
                ->orWhereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                })
                ->get();

            $missing = 0;
            foreach ($users as $user) {
                $hasRecord = match($role) {
                    'teacher' => $user->teacher()->exists(),
                    'student' => $user->student()->exists(),
                    'guardian' => $user->guardian()->exists(),
                    'accountant' => $user->accountant()->exists(),
                    default => true,
                };

                if (!$hasRecord) {
                    $missing++;
                    $totalMissing++;
                    $this->issues[] = [
                        'type' => 'Missing Role Record',
                        'role' => $role,
                        'user' => $user->email,
                        'user_id' => $user->id,
                    ];
                    $this->warn("  ❌ Missing {$role} record: {$user->name} ({$user->email})");

                    if ($fix) {
                        $this->warn("    ⚠️  Auto-fix for missing role records requires manual review");
                        $this->line("    Run: php artisan sync:user-roles --repair");
                    }
                }
            }

            if ($missing === 0) {
                $this->info("  ✅ All {$role} records exist");
            } else {
                $this->warn("  Found {$missing} missing {$role} records");
            }
        }

        $this->totalIssues += $totalMissing;
        $this->newLine();
    }

    /**
     * Check for orphaned records (records without users).
     * Comment 7: Use whereDoesntExist for better performance
     */
    private function checkOrphanedRecords(bool $fix): void
    {
        $this->info('📋 Check 3: Orphaned Records');
        $this->line('Checking for role records without corresponding users...');
        $this->newLine();

        $totalOrphaned = 0;

        // Comment 7: Use whereDoesntExist instead of whereNotIn with pluck
        $orphanedTeachers = Teacher::whereDoesntHave('user')->get();
        $orphanedStudents = Student::whereDoesntHave('user')->get();
        $orphanedGuardians = Guardian::whereDoesntHave('user')->get();
        $orphanedAccountants = Accountant::whereDoesntHave('user')->get();

        if ($orphanedTeachers->count() > 0) {
            $totalOrphaned += $orphanedTeachers->count();
            $this->warn("  ❌ Found {$orphanedTeachers->count()} orphaned teacher records");
            foreach ($orphanedTeachers as $teacher) {
                $this->issues[] = [
                    'type' => 'Orphaned Record',
                    'table' => 'teachers',
                    'id' => $teacher->id,
                    'user_id' => $teacher->user_id,
                ];
            }

            if ($fix) {
                $orphanedTeachers->each->delete();
                $this->fixed[] = "Deleted {$orphanedTeachers->count()} orphaned teacher records";
                $this->info("    ✅ Deleted orphaned teacher records");
            }
        } else {
            $this->info('  ✅ No orphaned teacher records');
        }

        if ($orphanedStudents->count() > 0) {
            $totalOrphaned += $orphanedStudents->count();
            $this->warn("  ❌ Found {$orphanedStudents->count()} orphaned student records");
            foreach ($orphanedStudents as $student) {
                $this->issues[] = [
                    'type' => 'Orphaned Record',
                    'table' => 'students',
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                ];
            }

            if ($fix) {
                $orphanedStudents->each->delete();
                $this->fixed[] = "Deleted {$orphanedStudents->count()} orphaned student records";
                $this->info("    ✅ Deleted orphaned student records");
            }
        } else {
            $this->info('  ✅ No orphaned student records');
        }

        if ($orphanedGuardians->count() > 0) {
            $totalOrphaned += $orphanedGuardians->count();
            $this->warn("  ❌ Found {$orphanedGuardians->count()} orphaned guardian records");
            foreach ($orphanedGuardians as $guardian) {
                $this->issues[] = [
                    'type' => 'Orphaned Record',
                    'table' => 'guardians',
                    'id' => $guardian->id,
                    'user_id' => $guardian->user_id,
                ];
            }

            if ($fix) {
                $orphanedGuardians->each->delete();
                $this->fixed[] = "Deleted {$orphanedGuardians->count()} orphaned guardian records";
                $this->info("    ✅ Deleted orphaned guardian records");
            }
        } else {
            $this->info('  ✅ No orphaned guardian records');
        }

        if ($orphanedAccountants->count() > 0) {
            $totalOrphaned += $orphanedAccountants->count();
            $this->warn("  ❌ Found {$orphanedAccountants->count()} orphaned accountant records");
            foreach ($orphanedAccountants as $accountant) {
                $this->issues[] = [
                    'type' => 'Orphaned Record',
                    'table' => 'accountants',
                    'id' => $accountant->id,
                    'user_id' => $accountant->user_id,
                ];
            }

            if ($fix) {
                $orphanedAccountants->each->delete();
                $this->fixed[] = "Deleted {$orphanedAccountants->count()} orphaned accountant records";
                $this->info("    ✅ Deleted orphaned accountant records");
            }
        } else {
            $this->info('  ✅ No orphaned accountant records');
        }

        $this->totalIssues += $totalOrphaned;
        $this->newLine();
    }

    /**
     * Check data integrity across the system.
     * Comment 6 & 7: Added exam/subject reference checks and use whereDoesntHave
     */
    private function checkDataIntegrity(bool $fix): void
    {
        $this->info('📋 Check 4: Data Integrity');
        $this->line('Checking for data inconsistencies...');
        $this->newLine();

        // Students without class assignment
        $studentsWithoutClass = Student::whereNull('class_id')->count();
        if ($studentsWithoutClass > 0) {
            $this->warn("  ⚠️  {$studentsWithoutClass} students without class assignment");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Students without class',
                'count' => $studentsWithoutClass,
            ];
            $this->totalIssues += $studentsWithoutClass;
        } else {
            $this->info('  ✅ All students have class assignments');
        }

        // Teachers without department
        $teachersWithoutDept = Teacher::whereNull('department_id')->count();
        if ($teachersWithoutDept > 0) {
            $this->warn("  ⚠️  {$teachersWithoutDept} teachers without department assignment");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Teachers without department',
                'count' => $teachersWithoutDept,
            ];
            $this->totalIssues += $teachersWithoutDept;
        } else {
            $this->info('  ✅ All teachers have department assignments');
        }

        // Subjects without teacher or class
        $subjectsWithoutTeacher = Subject::whereNull('teacher_id')->count();
        if ($subjectsWithoutTeacher > 0) {
            $this->warn("  ⚠️  {$subjectsWithoutTeacher} subjects without teacher assignment");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Subjects without teacher',
                'count' => $subjectsWithoutTeacher,
            ];
            $this->totalIssues += $subjectsWithoutTeacher;
        } else {
            $this->info('  ✅ All subjects have teacher assignments');
        }

        // Comment 7: Fees without student (use whereDoesntHave for performance)
        $feesWithoutStudent = Fee::whereDoesntHave('student')->count();
        if ($feesWithoutStudent > 0) {
            $this->warn("  ⚠️  {$feesWithoutStudent} fees linked to non-existent students");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Fees without valid student',
                'count' => $feesWithoutStudent,
            ];
            $this->totalIssues += $feesWithoutStudent;
        } else {
            $this->info('  ✅ All fees have valid student references');
        }

        // Comment 7: Results without student (use whereDoesntHave)
        $resultsWithoutStudent = Result::whereDoesntHave('student')->count();
        if ($resultsWithoutStudent > 0) {
            $this->warn("  ⚠️  {$resultsWithoutStudent} results linked to non-existent students");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Results without valid student',
                'count' => $resultsWithoutStudent,
            ];
            $this->totalIssues += $resultsWithoutStudent;
        } else {
            $this->info('  ✅ All results have valid student references');
        }

        // Comment 6: Add exam reference check
        $resultsWithoutExam = Result::whereDoesntHave('exam')->count();
        if ($resultsWithoutExam > 0) {
            $this->warn("  ⚠️  {$resultsWithoutExam} results linked to non-existent exams");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Results without valid exam',
                'count' => $resultsWithoutExam,
            ];
            $this->totalIssues += $resultsWithoutExam;
        } else {
            $this->info('  ✅ All results have valid exam references');
        }

        // Comment 6: Add subject reference check
        $resultsWithoutSubject = Result::whereDoesntHave('subject')->count();
        if ($resultsWithoutSubject > 0) {
            $this->warn("  ⚠️  {$resultsWithoutSubject} results linked to non-existent subjects");
            $this->issues[] = [
                'type' => 'Data Integrity',
                'issue' => 'Results without valid subject',
                'count' => $resultsWithoutSubject,
            ];
            $this->totalIssues += $resultsWithoutSubject;
        } else {
            $this->info('  ✅ All results have valid subject references');
        }

        $this->newLine();
    }

    /**
     * Display summary with color-coded output.
     */
    private function displaySummary(bool $fix): void
    {
        $this->newLine();
        $this->info('===========================================');
        $this->info('  Verification Summary');
        $this->info('===========================================');
        $this->newLine();

        if ($this->totalIssues === 0) {
            $this->info('🎉 <fg=green;options=bold>EXCELLENT!</> System integrity is perfect!');
            $this->info('   No issues found.');
        } elseif ($this->totalIssues < 5) {
            $this->warn('⚠️  <fg=yellow;options=bold>GOOD</> Minor issues detected');
            $this->line("   Total Issues: {$this->totalIssues}");
        } elseif ($this->totalIssues < 20) {
            $this->warn('⚠️  <fg=yellow;options=bold>WARNING</> Several issues need attention');
            $this->line("   Total Issues: {$this->totalIssues}");
        } else {
            $this->error('🚨 <fg=red;options=bold>CRITICAL</> Major integrity problems!');
            $this->line("   Total Issues: {$this->totalIssues}");
        }

        $this->newLine();

        if ($fix && count($this->fixed) > 0) {
            $this->info('✅ Auto-Fixed Issues:');
            foreach ($this->fixed as $fixedItem) {
                $this->line("  • {$fixedItem}");
            }
            $this->newLine();
        }

        if ($this->totalIssues > 0) {
            $this->warn('📝 Recommendations:');
            $this->line('  1. Review the detailed log file for all issues');
            $this->line('  2. Run: php artisan sync:user-roles --repair');
            $this->line('  3. Manually assign missing data (class, department, etc.)');
            $this->line('  4. Re-run this command to verify fixes');
            $this->newLine();
        }

        // Issue breakdown table
        if (count($this->issues) > 0) {
            $issueTypes = [];
            foreach ($this->issues as $issue) {
                $type = $issue['type'];
                if (!isset($issueTypes[$type])) {
                    $issueTypes[$type] = 0;
                }
                $issueTypes[$type]++;
            }

            $tableData = [];
            foreach ($issueTypes as $type => $count) {
                $tableData[] = [$type, $count];
            }

            $this->table(['Issue Type', 'Count'], $tableData);
        }
    }

    /**
     * Generate detailed log file.
     */
    private function generateLogFile(): void
    {
        $logDir = storage_path('logs/integrity');
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $filename = 'system-integrity-' . now()->format('Y-m-d_His') . '.log';
        $filepath = $logDir . '/' . $filename;

        $content = "System Integrity Verification Report\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('=', 80) . "\n\n";

        $content .= "Total Issues Found: {$this->totalIssues}\n";
        $content .= "Total Fixes Applied: " . count($this->fixed) . "\n\n";

        $content .= str_repeat('=', 80) . "\n";
        $content .= "DETAILED ISSUES\n";
        $content .= str_repeat('=', 80) . "\n\n";

        foreach ($this->issues as $index => $issue) {
            $content .= ($index + 1) . ". " . $issue['type'] . "\n";
            foreach ($issue as $key => $value) {
                if ($key !== 'type') {
                    $content .= "   {$key}: {$value}\n";
                }
            }
            $content .= "\n";
        }

        if (count($this->fixed) > 0) {
            $content .= str_repeat('=', 80) . "\n";
            $content .= "FIXES APPLIED\n";
            $content .= str_repeat('=', 80) . "\n\n";

            foreach ($this->fixed as $index => $fix) {
                $content .= ($index + 1) . ". {$fix}\n";
            }
        }

        file_put_contents($filepath, $content);

        $this->newLine();
        $this->info("📄 Detailed log saved to: {$filepath}");
        
        Log::info('System integrity check completed', [
            'total_issues' => $this->totalIssues,
            'fixes_applied' => count($this->fixed),
            'log_file' => $filepath
        ]);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use App\Models\Department;
use App\Models\ClassRoom;

class VerifyRoleRecords extends Command
{
    protected $signature = 'verify:role-records
                            {--repair : Create missing role records with default values}
                            {--role= : Verify only specific role (teacher, student, guardian, accountant)}
                            {--dry-run : Show what would be repaired without making changes}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Verify data integrity between users and role-specific tables';

    protected array $missingRecords = [];
    protected array $repairedRecords = [];
    protected array $errors = [];

    public function handle()
    {
        $repair = $this->option('repair');
        $specificRole = $this->option('role');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($specificRole && !in_array($specificRole, ['teacher', 'student', 'guardian', 'accountant'])) {
            $this->error("Invalid role: {$specificRole}");
            $this->error("Valid roles are: teacher, student, guardian, accountant");
            return self::FAILURE;
        }

        $this->info('==========================================================');
        $this->info('ROLE RECORDS VERIFICATION');
        $this->info('==========================================================');
        
        if ($dryRun) {
            $this->comment('Mode: DRY RUN (no changes will be made)');
        } elseif ($repair) {
            $this->comment('Mode: REPAIR (will create missing records)');
        } else {
            $this->comment('Mode: VERIFICATION ONLY');
        }
        
        $this->comment('Timestamp: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');

        Log::info('VerifyRoleRecords command started', [
            'repair' => $repair,
            'role' => $specificRole,
            'dry_run' => $dryRun,
            'force' => $force,
        ]);

        if ($repair && !$force && !$dryRun) {
            if (!$this->confirm('This will create missing role records. Continue?')) {
                $this->info('Aborting.');
                return self::SUCCESS;
            }
        }

        $rolesToCheck = $specificRole ? [$specificRole] : ['teacher', 'student', 'guardian', 'accountant'];

        foreach ($rolesToCheck as $role) {
            $this->verifyRole($role, $repair, $dryRun);
        }

        $this->displaySummary($repair, $dryRun);

        $totalMissing = array_sum(array_map('count', $this->missingRecords));
        $totalRepaired = array_sum(array_map('count', $this->repairedRecords));
        $totalErrors = count($this->errors);

        Log::info('VerifyRoleRecords command completed', [
            'total_missing' => $totalMissing,
            'total_repaired' => $totalRepaired,
            'total_errors' => $totalErrors,
        ]);

        return empty($this->errors) ? self::SUCCESS : self::FAILURE;
    }

    protected function verifyRole(string $role, bool $repair, bool $dryRun): void
    {
        $this->line('');
        $this->line(str_repeat('=', 60));
        $this->info("Verifying {$role} records...");

        $users = User::where('role', $role)->get();
        $this->comment("Found {$users->count()} users with role '{$role}'");

        Log::info("Verifying {$role} records", ['count' => $users->count()]);

        foreach ($users as $user) {
            $hasRecord = match($role) {
                'teacher' => $user->teacher()->exists(),
                'student' => $user->student()->exists(),
                'guardian' => $user->guardian()->exists(),
                'accountant' => $user->accountant()->exists(),
                default => false,
            };

            if (!$hasRecord) {
                $this->missingRecords[$role][] = $user;
            }
        }

        $missingCount = count($this->missingRecords[$role] ?? []);
        if ($missingCount > 0) {
            $this->warn("⚠ Found {$missingCount} missing {$role} records");
            $this->line('');

            $tableData = [];
            $displayLimit = 10;
            $missing = $this->missingRecords[$role];
            
            foreach (array_slice($missing, 0, $displayLimit) as $user) {
                $tableData[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            }

            $this->table(['ID', 'Name', 'Email', 'Created At'], $tableData);

            if ($missingCount > $displayLimit) {
                $this->comment("... and " . ($missingCount - $displayLimit) . " more");
            }

            if ($repair || $dryRun) {
                $this->repairMissingRecords($role, $dryRun);
            }
        } else {
            $this->info("✓ All {$role} records are intact");
        }
    }

    protected function repairMissingRecords(string $role, bool $dryRun): void
    {
        $missing = $this->missingRecords[$role] ?? [];
        if (empty($missing)) {
            return;
        }

        $this->line('');
        $this->info($dryRun ? "[DRY RUN] Would repair {$role} records:" : "Repairing {$role} records...");

        foreach ($missing as $user) {
            try {
                if (!$dryRun) {
                    DB::transaction(function () use ($user, $role) {
                        switch ($role) {
                            case 'teacher':
                                $this->repairTeacherRecord($user, false);
                                break;
                            case 'student':
                                $this->repairStudentRecord($user, false);
                                break;
                            case 'guardian':
                                $this->repairGuardianRecord($user, false);
                                break;
                            case 'accountant':
                                $this->repairAccountantRecord($user, false);
                                break;
                        }
                    });
                } else {
                    switch ($role) {
                        case 'teacher':
                            $this->repairTeacherRecord($user, true);
                            break;
                        case 'student':
                            $this->repairStudentRecord($user, true);
                            break;
                        case 'guardian':
                            $this->repairGuardianRecord($user, true);
                            break;
                        case 'accountant':
                            $this->repairAccountantRecord($user, true);
                            break;
                    }
                }
            } catch (\Throwable $e) {
                $errorMsg = "Failed to repair {$role} record for user {$user->id}: {$e->getMessage()}";
                $this->errors[] = $errorMsg;
                $this->error("✗ {$errorMsg}");
                Log::error("Failed to create {$role} record", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $repairedCount = count($this->repairedRecords[$role] ?? []);
        if ($dryRun) {
            $this->info("[DRY RUN] Would have repaired {$repairedCount} {$role} records");
        } else {
            $this->info("✓ Repaired {$repairedCount} {$role} records");
        }

        if (!empty($this->errors)) {
            $this->warn("⚠ Encountered " . count($this->errors) . " errors during repair");
        }
    }

    protected function repairTeacherRecord(User $user, bool $dryRun): void
    {
        if ($user->teacher()->exists()) {
            return;
        }

        $department = Department::firstOrCreate(
            ['code' => 'GEN'],
            [
                'name' => 'General',
                'description' => 'Default department for data integrity',
                'is_active' => true,
            ]
        );

        if (!$dryRun && $department->wasRecentlyCreated) {
            Log::info('Created default department', ['department_id' => $department->id]);
        }

        $maxRetries = 5;
        $employeeId = null;
        
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $employeeId = 'TEACH-' . now()->format('YmdHis') . '-' . $user->id . '-' . Str::lower(Str::random(4));
            
            if (!Teacher::where('employee_id', $employeeId)->exists()) {
                break;
            }
            
            if ($attempt === $maxRetries - 1) {
                throw new \Exception("Failed to generate unique employee_id after {$maxRetries} attempts");
            }
        }

        $data = [
            'user_id' => $user->id,
            'department_id' => $department->id,
            'employee_id' => $employeeId,
            'designation' => 'Teacher',
            'qualification' => 'To be updated',
            'phone' => $user->phone ?? 'N/A',
            'address' => 'To be updated',
            'joining_date' => now(),
            'salary' => 0,
            'is_active' => true,
        ];

        if ($dryRun) {
            $this->line("[DRY RUN] Would create teacher record for: {$user->name} (ID: {$user->id})");
            $this->repairedRecords['teacher'][] = $user;
        } else {
            Teacher::create($data);
            $this->repairedRecords['teacher'][] = $user;
            $this->line("✓ Created teacher record for: {$user->name} (ID: {$user->id})");
            Log::info('Created teacher record', [
                'user_id' => $user->id,
                'employee_id' => $employeeId,
            ]);
        }
    }

    protected function repairStudentRecord(User $user, bool $dryRun): void
    {
        if ($user->student()->exists()) {
            return;
        }

        $department = Department::firstOrCreate(
            ['code' => 'GEN'],
            [
                'name' => 'General',
                'description' => 'Default department for data integrity',
                'is_active' => true,
            ]
        );

        if (!$dryRun && $department->wasRecentlyCreated) {
            Log::info('Created default department', ['department_id' => $department->id]);
        }

        $class = ClassRoom::firstOrCreate(
            ['name' => 'General Class', 'section' => 'A'],
            [
                'department_id' => $department->id,
                'class_numeric' => '0',
                'capacity' => 0,
                'is_active' => true,
            ]
        );

        if (!$dryRun && $class->wasRecentlyCreated) {
            Log::info('Created default class', ['class_id' => $class->id]);
        }

        $maxRetries = 5;
        $admissionNumber = null;
        
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $admissionNumber = 'ADM-' . now()->format('Y') . '-' . now()->format('YmdHis') . '-' . $user->id . '-' . Str::lower(Str::random(4));
            
            if (!Student::where('admission_number', $admissionNumber)->exists()) {
                break;
            }
            
            if ($attempt === $maxRetries - 1) {
                throw new \Exception("Failed to generate unique admission_number after {$maxRetries} attempts");
            }
        }

        $data = [
            'user_id' => $user->id,
            'class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'admission_date' => now(),
            'father_name' => 'To be updated',
            'mother_name' => 'To be updated',
            'guardian_phone' => $user->phone ?? '0000000000',
            'guardian_email' => $user->email,
            'address' => 'To be updated',
            'date_of_birth' => now()->subYears(10),
            'gender' => 'other',
            'is_active' => true,
        ];

        if ($dryRun) {
            $this->line("[DRY RUN] Would create student record for: {$user->name} (ID: {$user->id})");
            $this->repairedRecords['student'][] = $user;
        } else {
            Student::create($data);
            $this->repairedRecords['student'][] = $user;
            $this->line("✓ Created student record for: {$user->name} (ID: {$user->id})");
            Log::info('Created student record', [
                'user_id' => $user->id,
                'admission_number' => $admissionNumber,
            ]);
        }
    }

    protected function repairGuardianRecord(User $user, bool $dryRun): void
    {
        if ($user->guardian()->exists()) {
            return;
        }

        $data = [
            'user_id' => $user->id,
            'phone' => $user->phone ?? '0000000000',
            'email' => $user->email,
            'address' => 'To be updated',
            'relationship_type' => 'other',
            'is_primary_contact' => true,
            'emergency_contact' => false,
            'is_active' => true,
        ];

        if ($dryRun) {
            $this->line("[DRY RUN] Would create guardian record for: {$user->name} (ID: {$user->id})");
            $this->repairedRecords['guardian'][] = $user;
        } else {
            Guardian::create($data);
            $this->repairedRecords['guardian'][] = $user;
            $this->line("✓ Created guardian record for: {$user->name} (ID: {$user->id})");
            Log::info('Created guardian record', ['user_id' => $user->id]);
        }
    }

    protected function repairAccountantRecord(User $user, bool $dryRun): void
    {
        if ($user->accountant()->exists()) {
            return;
        }

        $maxRetries = 5;
        $employeeId = null;
        
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $employeeId = 'ACC-' . now()->format('YmdHis') . '-' . $user->id . '-' . Str::lower(Str::random(4));
            
            if (!Accountant::where('employee_id', $employeeId)->exists()) {
                break;
            }
            
            if ($attempt === $maxRetries - 1) {
                throw new \Exception("Failed to generate unique employee_id after {$maxRetries} attempts");
            }
        }

        $data = [
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'designation' => 'Accountant',
            'qualification' => 'To be updated',
            'phone' => $user->phone ?? 'N/A',
            'address' => 'To be updated',
            'joining_date' => now(),
            'salary' => 0,
            'can_approve_waivers' => false,
            'can_approve_refunds' => false,
            'is_active' => true,
        ];

        if ($dryRun) {
            $this->line("[DRY RUN] Would create accountant record for: {$user->name} (ID: {$user->id})");
            $this->repairedRecords['accountant'][] = $user;
        } else {
            Accountant::create($data);
            $this->repairedRecords['accountant'][] = $user;
            $this->line("✓ Created accountant record for: {$user->name} (ID: {$user->id})");
            Log::info('Created accountant record', [
                'user_id' => $user->id,
                'employee_id' => $employeeId,
            ]);
        }
    }

    protected function displaySummary(bool $repair, bool $dryRun): void
    {
        $this->line('');
        $this->line(str_repeat('=', 60));
        $this->info('VERIFICATION SUMMARY');
        $this->line(str_repeat('=', 60));

        $totalMissing = array_sum(array_map('count', $this->missingRecords));
        $totalRepaired = array_sum(array_map('count', $this->repairedRecords));
        $totalErrors = count($this->errors);

        foreach (['teacher', 'student', 'guardian', 'accountant'] as $role) {
            $missing = count($this->missingRecords[$role] ?? []);
            $repaired = count($this->repairedRecords[$role] ?? []);
            
            $line = ucfirst($role) . ": {$missing} missing";
            if ($repair || $dryRun) {
                $line .= ", {$repaired} " . ($dryRun ? 'would be repaired' : 'repaired');
            }
            $this->line($line);
        }

        $this->line('');
        $this->info("Total missing records: {$totalMissing}");
        
        if ($repair || $dryRun) {
            $this->info(($dryRun ? 'Would repair: ' : 'Repaired: ') . $totalRepaired);
        }
        
        if ($totalErrors > 0) {
            $this->error("Errors encountered: {$totalErrors}");
        }

        if ($totalMissing > 0 && !$repair) {
            $this->line('');
            $this->comment('Run with --repair flag to create missing records');
            $this->comment('Run with --repair --dry-run to preview changes');
        }

        if ($repair && $totalRepaired > 0) {
            $this->line('');
            $this->warn('⚠ Created records have placeholder values');
            $this->warn('⚠ Please update these records with actual information');
        }

        if ($totalErrors > 0) {
            $this->line('');
            $this->error('Errors:');
            foreach ($this->errors as $error) {
                $this->line("  - {$error}");
            }
        }

        $this->line(str_repeat('=', 60));
    }
}

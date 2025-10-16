<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class SyncRolesToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user-roles 
                            {--role= : Sync only specific role (admin, teacher, student, staff, guardian, accountant)}
                            {--repair : Automatically create missing role records}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync legacy role column to Spatie roles and create missing role records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('  Role Synchronization Command');
        $this->info('===========================================');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $repair = $this->option('repair');
        $specificRole = $this->option('role');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        if ($repair && !$dryRun) {
            $this->warn('🔧 REPAIR MODE - Missing role records will be created');
            $this->newLine();
        }

        // Step 1: Sync Spatie Roles
        $this->info('Step 1: Syncing Spatie Roles...');
        $this->newLine();
        $spatieStats = $this->syncSpatieRoles($specificRole, $dryRun);

        // Step 2: Check and Create Missing Role Records
        $this->info('Step 2: Checking Role Records...');
        $this->newLine();
        $recordStats = $this->checkRoleRecords($specificRole, $repair, $dryRun);

        // Summary
        $this->newLine();
        $this->info('===========================================');
        $this->info('  Summary');
        $this->info('===========================================');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users Processed', $spatieStats['total']],
                ['Spatie Roles Assigned', $spatieStats['assigned']],
                ['Already Had Spatie Role', $spatieStats['skipped']],
                ['Missing Role Records Found', $recordStats['missing']],
                ['Role Records Created', $recordStats['created']],
                ['Errors Encountered', $spatieStats['errors'] + $recordStats['errors']],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. No changes were made.');
            $this->info('Run without --dry-run to apply changes.');
        }

        if ($recordStats['missing'] > 0 && !$repair && !$dryRun) {
            $this->newLine();
            $this->warn('⚠️  Missing role records detected!');
            $this->info('Run with --repair flag to automatically create them:');
            $this->line('  php artisan sync:user-roles --repair');
        }

        return Command::SUCCESS;
    }

    /**
     * Sync legacy role column to Spatie roles.
     */
    private function syncSpatieRoles(?string $specificRole, bool $dryRun): array
    {
        $query = User::query();
        
        if ($specificRole) {
            $query->where('role', $specificRole);
        }

        $users = $query->get();
        $total = $users->count();
        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        $this->withProgressBar($users, function ($user) use (&$assigned, &$skipped, &$errors, $dryRun) {
            try {
                $roleName = $user->role;
                
                // Comment 4: Validate role name - skip invalid/empty roles
                if (empty($roleName) || !in_array($roleName, ['admin', 'teacher', 'student', 'staff', 'guardian', 'accountant'])) {
                    $errors++;
                    Log::warning('User has invalid or empty role', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'role' => $roleName
                    ]);
                    return;
                }
                
                // Comment 1: Check if user already has this Spatie role (Spatie-only semantics)
                if ($user->getRoleNames()->contains($roleName)) {
                    $skipped++;
                    return;
                }

                // Ensure role exists in Spatie roles table
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

                if (!$dryRun) {
                    $user->assignRole($role);
                    
                    // Comment 3: Update sync tracking flags if columns exist
                    try {
                        if (Schema::hasColumn('users', 'spatie_role_synced')) {
                            $user->update([
                                'spatie_role_synced' => true,
                                'spatie_role_synced_at' => now(),
                            ]);
                        }
                    } catch (\Throwable $e) {
                        // Ignore if columns don't exist (optional migration)
                    }
                    
                    Log::info('Assigned Spatie role', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'role' => $roleName
                    ]);
                }

                $assigned++;
            } catch (\Throwable $e) {
                $errors++;
                Log::error('Failed to assign Spatie role', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        });

        $this->newLine(2);

        return compact('total', 'assigned', 'skipped', 'errors');
    }

    /**
     * Check for missing role records and optionally create them.
     */
    private function checkRoleRecords(?string $specificRole, bool $repair, bool $dryRun): array
    {
        $missing = 0;
        $created = 0;
        $errors = 0;

        $rolesToCheck = $specificRole ? [$specificRole] : ['teacher', 'student', 'guardian', 'accountant'];

        foreach ($rolesToCheck as $role) {
            $this->line("Checking {$role} records...");

            $users = User::where('role', $role)
                ->orWhereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                })
                ->get();

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
                    $this->warn("  ❌ Missing {$role} record for: {$user->name} ({$user->email})");

                    if ($repair && !$dryRun) {
                        try {
                            $this->createRoleRecord($user, $role);
                            $created++;
                            $this->info("  ✅ Created {$role} record for: {$user->name}");
                        } catch (\Throwable $e) {
                            $errors++;
                            $this->error("  ❌ Failed to create {$role} record: " . $e->getMessage());
                            Log::error("Failed to create {$role} record", [
                                'user_id' => $user->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }
        }

        return compact('missing', 'created', 'errors');
    }

    /**
     * Create missing role record with default values.
     */
    private function createRoleRecord(User $user, string $role): void
    {
        DB::transaction(function () use ($user, $role) {
            switch ($role) {
                case 'teacher':
                    Teacher::create([
                        'user_id' => $user->id,
                        'employee_id' => 'T' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'department_id' => null, // Admin should assign
                        'designation' => 'Teacher',
                        'qualification' => 'To be updated',
                        'joining_date' => now(),
                        'salary' => 0,
                        'is_active' => true,
                    ]);
                    break;

                case 'student':
                    Student::create([
                        'user_id' => $user->id,
                        'student_id' => 'S' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'class_id' => null, // Admin should assign
                        'roll_number' => null,
                        'admission_date' => now(),
                        'date_of_birth' => null,
                        'gender' => 'male',
                        'blood_group' => null,
                        'address' => 'To be updated',
                        'is_active' => true,
                    ]);
                    break;

                case 'guardian':
                    Guardian::create([
                        'user_id' => $user->id,
                        'guardian_id' => 'G' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'relation' => 'parent',
                        'occupation' => 'To be updated',
                        'address' => 'To be updated',
                        'is_active' => true,
                    ]);
                    break;

                case 'accountant':
                    Accountant::create([
                        'user_id' => $user->id,
                        'employee_id' => 'A' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'designation' => 'Accountant',
                        'joining_date' => now(),
                        'salary' => 0,
                        'can_approve_waivers' => false,
                        'max_waiver_amount' => null,
                        'is_active' => true,
                    ]);
                    break;
            }

            Log::info("Created {$role} record", [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $role
            ]);
        });
    }
}

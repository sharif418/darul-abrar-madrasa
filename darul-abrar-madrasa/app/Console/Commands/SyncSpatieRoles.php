<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SyncSpatieRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:spatie-roles
                            {--repair : Sync Spatie roles to match legacy role column}
                            {--role= : Sync only specific role (admin, teacher, student, staff, guardian, accountant)}
                            {--dry-run : Show what would be changed without making changes}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Spatie roles with legacy users.role column';

    /**
     * Users without Spatie roles
     *
     * @var array
     */
    protected array $missingRoles = [];

    /**
     * Users with wrong Spatie roles
     *
     * @var array
     */
    protected array $mismatchedRoles = [];

    /**
     * Users with multiple Spatie roles
     *
     * @var array
     */
    protected array $extraRoles = [];

    /**
     * Users successfully synced
     *
     * @var array
     */
    protected array $syncedUsers = [];

    /**
     * Errors encountered
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * Valid roles in the system
     *
     * @var array
     */
    protected array $validRoles = ['admin', 'teacher', 'student', 'staff', 'guardian', 'accountant'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get and validate options
        $repair = $this->option('repair');
        $specificRole = $this->option('role');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Validate specific role if provided
        if ($specificRole && !in_array($specificRole, $this->validRoles)) {
            $this->error("Invalid role: {$specificRole}");
            $this->error("Valid roles: " . implode(', ', $this->validRoles));
            return self::FAILURE;
        }

        // Display header
        $this->line('==========================================================');
        $this->info('SPATIE ROLES SYNCHRONIZATION');
        $this->line('==========================================================');
        
        $mode = $dryRun ? 'DRY RUN' : ($repair ? 'REPAIR MODE' : 'VERIFICATION ONLY');
        $this->comment("Mode: {$mode}");
        $this->comment('Timestamp: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');

        // Log command start
        Log::info('SyncSpatieRoles: Command started', [
            'repair' => $repair,
            'specific_role' => $specificRole,
            'dry_run' => $dryRun,
            'force' => $force,
        ]);

        // Ensure roles exist
        $this->ensureRolesExist();

        // Confirmation prompt
        if ($repair && !$force && !$dryRun) {
            if (!$this->confirm('This will modify Spatie role assignments. Continue?')) {
                $this->warn('Aborting...');
                return self::SUCCESS;
            }
        }

        // Determine roles to check
        $rolesToCheck = $specificRole ? [$specificRole] : $this->validRoles;

        // Verification loop
        foreach ($rolesToCheck as $role) {
            $this->verifyRole($role, $repair, $dryRun);
        }

        // Display summary
        $this->displaySummary($repair, $dryRun);

        // Log completion
        Log::info('SyncSpatieRoles: Command completed', [
            'total_missing' => array_sum(array_map('count', $this->missingRoles)),
            'total_mismatched' => array_sum(array_map(fn($m) => count($m), $this->mismatchedRoles)),
            'total_extra' => array_sum(array_map(fn($e) => count($e), $this->extraRoles)),
            'total_synced' => array_sum(array_map('count', $this->syncedUsers)),
            'total_errors' => count($this->errors),
        ]);

        return empty($this->errors) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Ensure all roles exist in Spatie
     */
    protected function ensureRolesExist(): void
    {
        $createdRoles = [];
        $guardName = config('auth.defaults.guard', 'web');

        foreach ($this->validRoles as $role) {
            $roleModel = Role::firstOrCreate(
                ['name' => $role, 'guard_name' => $guardName],
                ['name' => $role, 'guard_name' => $guardName]
            );
            
            if ($roleModel->wasRecentlyCreated) {
                $createdRoles[] = $role;
                Log::info("SyncSpatieRoles: Created missing Spatie role: {$role}", [
                    'guard_name' => $guardName,
                ]);
            }
        }

        if (!empty($createdRoles)) {
            $this->info('Created missing Spatie roles: ' . implode(', ', $createdRoles));
            $this->line('');
        }
    }

    /**
     * Verify role synchronization for a specific role
     */
    protected function verifyRole(string $role, bool $repair, bool $dryRun): void
    {
        // Display section header
        $this->line('============================================================');
        $this->info("Verifying {$role} role synchronization...");

        // Get total count first
        $totalCount = User::where('role', $role)->count();
        $this->comment("Found {$totalCount} users with legacy role '{$role}'");

        // Log verification start
        Log::info("SyncSpatieRoles: Verifying role", [
            'role' => $role,
            'user_count' => $totalCount,
        ]);

        // Initialize arrays for this role if not exists
        if (!isset($this->missingRoles[$role])) {
            $this->missingRoles[$role] = [];
        }
        if (!isset($this->mismatchedRoles[$role])) {
            $this->mismatchedRoles[$role] = [];
        }
        if (!isset($this->extraRoles[$role])) {
            $this->extraRoles[$role] = [];
        }

        // Process users in chunks for better memory management
        User::where('role', $role)
            ->with('roles')
            ->chunkById(100, function ($users) use ($role) {
                foreach ($users as $user) {
                    $spatieRoles = $user->roles->pluck('name')->toArray();

                    // Determine sync status
                    if (empty($spatieRoles)) {
                        // Case 1: No Spatie roles
                        $this->missingRoles[$role][] = $user;
                    } elseif (count($spatieRoles) === 1 && $spatieRoles[0] === $role) {
                        // Case 2: Correct single role (synced)
                        // No action needed
                    } elseif (count($spatieRoles) === 1 && $spatieRoles[0] !== $role) {
                        // Case 3: Wrong single role
                        $this->mismatchedRoles[$role][] = [
                            'user' => $user,
                            'current_role' => $spatieRoles[0],
                            'expected_role' => $role,
                        ];
                    } else {
                        // Case 4: Multiple Spatie roles
                        $this->extraRoles[$role][] = [
                            'user' => $user,
                            'spatie_roles' => $spatieRoles,
                            'expected_role' => $role,
                        ];
                    }
                }
            });

        // Display issues
        $missing = $this->missingRoles[$role] ?? [];
        $mismatched = $this->mismatchedRoles[$role] ?? [];
        $extra = $this->extraRoles[$role] ?? [];

        if (!empty($missing)) {
            $this->warn("⚠ Found " . count($missing) . " users missing Spatie role");
            $this->displayMissingRolesTable($missing);
        }

        if (!empty($mismatched)) {
            $this->warn("⚠ Found " . count($mismatched) . " users with mismatched Spatie roles");
            $this->displayMismatchedRolesTable($mismatched);
        }

        if (!empty($extra)) {
            $this->warn("⚠ Found " . count($extra) . " users with multiple Spatie roles");
            $this->displayExtraRolesTable($extra);
        }

        // Repair if requested
        if ($repair || $dryRun) {
            $this->syncRoles($role, $dryRun);
        }

        // Display success if no issues
        if (empty($missing) && empty($mismatched) && empty($extra)) {
            $this->info("✓ All {$role} users have correct Spatie roles");
        }

        $this->line('');
    }

    /**
     * Display table of users missing Spatie roles
     */
    protected function displayMissingRolesTable(array $users): void
    {
        $rows = array_slice(array_map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
            ];
        }, $users), 0, 10);

        $this->table(['ID', 'Name', 'Email', 'Legacy Role'], $rows);

        if (count($users) > 10) {
            $this->comment('... and ' . (count($users) - 10) . ' more');
        }
        $this->line('');
    }

    /**
     * Display table of users with mismatched roles
     */
    protected function displayMismatchedRolesTable(array $mismatched): void
    {
        $rows = array_slice(array_map(function ($item) {
            return [
                $item['user']->id,
                $item['user']->name,
                $item['current_role'],
                $item['expected_role'],
            ];
        }, $mismatched), 0, 10);

        $this->table(['ID', 'Name', 'Current Spatie Role', 'Expected Role'], $rows);

        if (count($mismatched) > 10) {
            $this->comment('... and ' . (count($mismatched) - 10) . ' more');
        }
        $this->line('');
    }

    /**
     * Display table of users with extra roles
     */
    protected function displayExtraRolesTable(array $extra): void
    {
        $rows = array_slice(array_map(function ($item) {
            return [
                $item['user']->id,
                $item['user']->name,
                implode(', ', $item['spatie_roles']),
                $item['expected_role'],
            ];
        }, $extra), 0, 10);

        $this->table(['ID', 'Name', 'Spatie Roles', 'Expected Role'], $rows);

        if (count($extra) > 10) {
            $this->comment('... and ' . (count($extra) - 10) . ' more');
        }
        $this->line('');
    }

    /**
     * Sync Spatie roles for users with issues
     */
    protected function syncRoles(string $role, bool $dryRun): void
    {
        // Get all issues
        $missing = $this->missingRoles[$role] ?? [];
        $mismatched = array_column($this->mismatchedRoles[$role] ?? [], 'user');
        $extra = array_column($this->extraRoles[$role] ?? [], 'user');
        $allIssues = array_merge($missing, $mismatched, $extra);

        if (empty($allIssues)) {
            return;
        }

        // Display sync header
        $header = $dryRun ? "[DRY RUN] Would sync {$role} roles..." : "Syncing {$role} roles...";
        $this->comment($header);

        // Sync each user
        foreach ($allIssues as $user) {
            if ($dryRun) {
                // Dry run - just display what would happen
                $this->line("[DRY RUN] Would sync user {$user->id}: {$user->name} - assign '{$role}' Spatie role");
                $this->syncedUsers[$role][] = $user;
            } else {
                // Repair - actually sync the role
                try {
                    DB::transaction(function () use ($user, $role) {
                        $oldRoles = $user->roles->pluck('name')->toArray();
                        $user->syncRoles([$role]);
                        
                        Log::info('SyncSpatieRoles: Synced Spatie role', [
                            'user_id' => $user->id,
                            'old_roles' => $oldRoles,
                            'new_role' => $role,
                        ]);
                    });

                    $this->syncedUsers[$role][] = $user;
                    $this->info("✓ Synced user {$user->id}: {$user->name} - assigned '{$role}' Spatie role");
                } catch (\Throwable $e) {
                    $this->errors[] = "Failed to sync user {$user->id}: {$e->getMessage()}";
                    $this->error("✗ Failed to sync user {$user->id}: {$e->getMessage()}");
                    
                    Log::error('SyncSpatieRoles: Failed to sync role', [
                        'user_id' => $user->id,
                        'role' => $role,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Display sync summary
        $syncedCount = count($this->syncedUsers[$role] ?? []);
        $this->comment("Synced {$syncedCount} users");
        
        if (!empty($this->errors)) {
            $this->warn("Errors encountered: " . count($this->errors));
        }
        
        $this->line('');
    }

    /**
     * Display summary of synchronization
     */
    protected function displaySummary(bool $repair, bool $dryRun): void
    {
        // Display header
        $this->line('============================================================');
        $this->info('SYNCHRONIZATION SUMMARY');
        $this->line('============================================================');

        // Calculate totals
        $totalMissing = array_sum(array_map('count', $this->missingRoles));
        $totalMismatched = array_sum(array_map(fn($m) => count($m), $this->mismatchedRoles));
        $totalExtra = array_sum(array_map(fn($e) => count($e), $this->extraRoles));
        $totalIssues = $totalMissing + $totalMismatched + $totalExtra;
        $totalSynced = array_sum(array_map('count', $this->syncedUsers));
        $totalErrors = count($this->errors);

        // Display statistics by role
        foreach ($this->validRoles as $role) {
            $missing = count($this->missingRoles[$role] ?? []);
            $mismatched = count($this->mismatchedRoles[$role] ?? []);
            $extra = count($this->extraRoles[$role] ?? []);
            $synced = count($this->syncedUsers[$role] ?? []);

            $line = ucfirst($role) . ": {$missing} missing, {$mismatched} mismatched, {$extra} extra";
            if ($repair || $dryRun) {
                $line .= ", {$synced} synced";
            }
            $this->line($line);
        }

        $this->line('');

        // Display overall totals
        $this->comment("Total issues found: {$totalIssues}");
        if ($repair || $dryRun) {
            $this->comment("Total synced: {$totalSynced}");
        }
        if ($totalErrors > 0) {
            $this->error("Errors encountered: {$totalErrors}");
        }

        $this->line('');

        // Display recommendations
        if ($totalIssues > 0 && !$repair) {
            $this->info('Run with --repair flag to sync roles');
            $this->info('Run with --repair --dry-run to preview changes');
        } elseif ($repair && $totalSynced > 0) {
            $this->info('✓ Spatie roles synchronized successfully');
            $this->info('Users can now use Spatie permission system');
        }

        if ($totalErrors > 0) {
            $this->line('');
            $this->error('Errors:');
            foreach ($this->errors as $error) {
                $this->error('  - ' . $error);
            }
        }

        $this->line('');

        // Display migration status
        $totalUsers = User::count();
        $usersWithRoles = User::has('roles')->count();
        $percentage = $totalUsers > 0 ? round(($usersWithRoles / $totalUsers) * 100, 1) : 0;
        
        $this->comment("Migration Progress: {$percentage}% ({$usersWithRoles}/{$totalUsers} users have Spatie roles)");
        $this->line('');
    }
}

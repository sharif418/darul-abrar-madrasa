<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            
            // Department Management
            'view-departments',
            'create-departments',
            'edit-departments',
            'delete-departments',
            
            // Class Management
            'view-classes',
            'create-classes',
            'edit-classes',
            'delete-classes',
            
            // Subject Management
            'view-subjects',
            'create-subjects',
            'edit-subjects',
            'delete-subjects',
            
            // Student Management
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'view-own-profile',
            'edit-own-profile',
            
            // Teacher Management
            'view-teachers',
            'create-teachers',
            'edit-teachers',
            'delete-teachers',
            
            // Attendance Management
            'view-attendance',
            'create-attendance',
            'edit-attendance',
            'delete-attendance',
            'view-own-attendance',
            
            // Exam Management
            'view-exams',
            'create-exams',
            'edit-exams',
            'delete-exams',
            'publish-results',
            
            // Result Management
            'view-results',
            'create-results',
            'edit-results',
            'delete-results',
            'view-own-results',
            
            // Fee Management
            'view-fees',
            'create-fees',
            'edit-fees',
            'delete-fees',
            'record-payment',
            'generate-invoice',
            'view-fee-reports',
            'view-own-fees',
            
            // Notice Management
            'view-notices',
            'create-notices',
            'edit-notices',
            'delete-notices',
            
            // Grading Scale Management
            'view-grading-scales',
            'create-grading-scales',
            'edit-grading-scales',
            'delete-grading-scales',
            
            // Lesson Plan Management
            'view-lesson-plans',
            'create-lesson-plans',
            'edit-lesson-plans',
            'delete-lesson-plans',
            
            // Study Material Management
            'view-study-materials',
            'create-study-materials',
            'edit-study-materials',
            'delete-study-materials',
            'download-study-materials',
            
            // Dashboard Access
            'view-admin-dashboard',
            'view-teacher-dashboard',
            'view-student-dashboard',

            // Guardian permissions
            'view-guardian-dashboard',
            'view-children-info',
            'view-children-attendance',
            'view-children-results',
            'view-children-fees',
            'pay-fees-online',
            'message-teachers',

            // Accountant permissions
            'view-accountant-dashboard',
            'manage-fee-waivers',
            'approve-waivers',
            'manage-installments',
            'apply-late-fees',
            'generate-financial-reports',
            'reconcile-payments',
            'manage-late-fee-policies',
            'view-audit-logs',
            'export-financial-data',

            // Library permissions (future)
            'view-library-dashboard',
            'manage-books',
            'issue-books',
            'return-books',
            'manage-fines',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['name' => $permission]);
        }

        // Get guard name from config
        $guardName = config('auth.defaults.guard', 'web');

        // Create roles and assign permissions

        // Admin Role - Full Access
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => $guardName],
            ['name' => 'admin', 'guard_name' => $guardName]
        );
        $adminRole->syncPermissions(Permission::all());

        // Teacher Role - Limited Access
        $teacherRole = Role::firstOrCreate(
            ['name' => 'teacher', 'guard_name' => $guardName],
            ['name' => 'teacher', 'guard_name' => $guardName]
        );
        $teacherRole->syncPermissions([
            // View access
            'view-students',
            'view-classes',
            'view-subjects',
            'view-departments',
            'view-teachers',
            
            // Attendance
            'view-attendance',
            'create-attendance',
            'edit-attendance',
            
            // Exams
            'view-exams',
            
            // Results
            'view-results',
            'create-results',
            'edit-results',
            
            // Lesson Plans
            'view-lesson-plans',
            'create-lesson-plans',
            'edit-lesson-plans',
            'delete-lesson-plans',
            
            // Study Materials
            'view-study-materials',
            'create-study-materials',
            'edit-study-materials',
            'delete-study-materials',
            
            // Notices
            'view-notices',
            
            // Dashboard
            'view-teacher-dashboard',
        ]);

        // Student Role - View Only Access
        $studentRole = Role::firstOrCreate(
            ['name' => 'student', 'guard_name' => $guardName],
            ['name' => 'student', 'guard_name' => $guardName]
        );
        $studentRole->syncPermissions([
            // Own profile
            'view-own-profile',
            'edit-own-profile',
            
            // Own attendance
            'view-own-attendance',
            
            // Own results
            'view-own-results',
            
            // Own fees
            'view-own-fees',
            
            // Study materials
            'view-study-materials',
            'download-study-materials',
            
            // Notices
            'view-notices',
            
            // Dashboard
            'view-student-dashboard',
        ]);

        // Staff Role (Optional - for future use)
        $staffRole = Role::firstOrCreate(
            ['name' => 'staff', 'guard_name' => $guardName],
            ['name' => 'staff', 'guard_name' => $guardName]
        );
        $staffRole->syncPermissions([
            'view-students',
            'view-teachers',
            'view-classes',
            'view-subjects',
            'view-departments',
            'view-notices',
        ]);

        // Guardian Role
        $guardianRole = Role::firstOrCreate(
            ['name' => 'guardian', 'guard_name' => $guardName],
            ['name' => 'guardian', 'guard_name' => $guardName]
        );
        $guardianRole->syncPermissions([
            'view-guardian-dashboard',
            'view-children-info',
            'view-children-attendance',
            'view-children-results',
            'view-children-fees',
            'pay-fees-online',
            'view-notices',
            'download-study-materials',
        ]);

        // Accountant Role
        $accountantRole = Role::firstOrCreate(
            ['name' => 'accountant', 'guard_name' => $guardName],
            ['name' => 'accountant', 'guard_name' => $guardName]
        );
        $accountantRole->syncPermissions([
            'view-accountant-dashboard',
            'view-fees',
            'create-fees',
            'edit-fees',
            'record-payment',
            'generate-invoice',
            'view-fee-reports',
            'manage-fee-waivers',
            'approve-waivers',
            'manage-installments',
            'apply-late-fees',
            'generate-financial-reports',
            'reconcile-payments',
            'manage-late-fee-policies',
            'view-audit-logs',
            'export-financial-data',
            'view-students',
        ]);

        // Verify all roles exist before assignment
        $this->verifyRolesExist();

        // Get users by role
        $adminUsers = User::where('role', 'admin')->get();
        $teacherUsers = User::where('role', 'teacher')->get();
        $studentUsers = User::where('role', 'student')->get();
        $staffUsers = User::where('role', 'staff')->get();
        $guardianUsers = User::where('role', 'guardian')->get();
        $accountantUsers = User::where('role', 'accountant')->get();

        // Enhanced role assignment with error handling
        $totalProcessed = 0;
        $totalAssigned = 0;
        $errorCount = 0;

        $roleAssignments = [
            'admin' => $adminUsers,
            'teacher' => $teacherUsers,
            'student' => $studentUsers,
            'staff' => $staffUsers,
            'guardian' => $guardianUsers,
            'accountant' => $accountantUsers,
        ];

        foreach ($roleAssignments as $roleName => $users) {
            foreach ($users as $user) {
                $totalProcessed++;
                // Use spatieHasRole() to check only Spatie roles, not legacy fallback
                if (!$user->spatieHasRole($roleName)) {
                    try {
                        $user->assignRole($roleName);
                        $totalAssigned++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->command->warn("Failed to assign {$roleName} role to user {$user->id}: {$e->getMessage()}");
                        \Log::error('RolePermissionSeeder: Failed to assign role', [
                            'user_id' => $user->id,
                            'role' => $roleName,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Admin users: ' . $adminUsers->count());
        $this->command->info('Teacher users: ' . $teacherUsers->count());
        $this->command->info('Student users: ' . $studentUsers->count());
        $this->command->info('Staff users: ' . $staffUsers->count());
        $this->command->info('Guardian users: ' . $guardianUsers->count());
        $this->command->info('Accountant users: ' . $accountantUsers->count());
        $this->command->info('Total users processed: ' . $totalProcessed);
        $this->command->info('Total Spatie roles assigned: ' . $totalAssigned);
        
        if ($errorCount > 0) {
            $this->command->warn('Errors encountered: ' . $errorCount);
        }
        
        $this->command->info('Run php artisan sync:spatie-roles to verify synchronization');
    }

    /**
     * Verify all required roles exist
     */
    private function verifyRolesExist(): void
    {
        $expectedRoles = ['admin', 'teacher', 'student', 'staff', 'guardian', 'accountant'];
        
        foreach ($expectedRoles as $role) {
            if (!Role::where('name', $role)->exists()) {
                $this->command->error("Role '{$role}' does not exist!");
                throw new \Exception("Required role '{$role}' is missing from the database");
            }
        }
        
        $this->command->info('All required roles verified');
    }
}

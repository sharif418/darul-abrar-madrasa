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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin Role - Full Access
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Teacher Role - Limited Access
        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['name' => 'teacher']);
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
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['name' => 'student']);
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
        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['name' => 'staff']);
        $staffRole->syncPermissions([
            'view-students',
            'view-teachers',
            'view-classes',
            'view-subjects',
            'view-departments',
            'view-notices',
        ]);

        // Assign admin role to existing admin users
        $adminUsers = User::where('role', 'admin')->get();
        foreach ($adminUsers as $user) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
        }

        // Assign teacher role to existing teacher users
        $teacherUsers = User::where('role', 'teacher')->get();
        foreach ($teacherUsers as $user) {
            if (!$user->hasRole('teacher')) {
                $user->assignRole('teacher');
            }
        }

        // Assign student role to existing student users
        $studentUsers = User::where('role', 'student')->get();
        foreach ($studentUsers as $user) {
            if (!$user->hasRole('student')) {
                $user->assignRole('student');
            }
        }

        // Assign staff role to existing staff users
        $staffUsers = User::where('role', 'staff')->get();
        foreach ($staffUsers as $user) {
            if (!$user->hasRole('staff')) {
                $user->assignRole('staff');
            }
        }

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Admin users: ' . $adminUsers->count());
        $this->command->info('Teacher users: ' . $teacherUsers->count());
        $this->command->info('Student users: ' . $studentUsers->count());
        $this->command->info('Staff users: ' . $staffUsers->count());
    }
}

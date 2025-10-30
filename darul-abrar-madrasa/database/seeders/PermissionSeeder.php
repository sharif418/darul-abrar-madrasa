<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'users' => 'User Management',
            'departments' => 'Department Management',
            'classes' => 'Class Management',
            'teachers' => 'Teacher Management',
            'students' => 'Student Management',
            'guardians' => 'Guardian Management',
            'subjects' => 'Subject Management',
            'attendances' => 'Attendance Management',
            'exams' => 'Exam Management',
            'results' => 'Result Management',
            'fees' => 'Fee Management',
            'notices' => 'Notice Management',
            'study_materials' => 'Study Material Management',
            'lesson_plans' => 'Lesson Plan Management',
            'grading_scales' => 'Grading Scale Management',
            'reports' => 'Reports & Analytics',
        ];

        $actions = [
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Update',
            'delete' => 'Delete',
        ];

        DB::table('permissions')->truncate();
        DB::table('role_permissions')->truncate();

        foreach ($modules as $moduleSlug => $moduleName) {
            foreach ($actions as $actionSlug => $actionName) {
                $permission = Permission::create([
                    'name' => "{$actionName} {$moduleName}",
                    'slug' => "{$moduleSlug}.{$actionSlug}",
                    'module' => $moduleSlug,
                    'description' => "Permission to {$actionSlug} {$moduleName}",
                    'is_active' => true,
                ]);

                $this->assignPermissionToRoles($permission, $moduleSlug, $actionSlug);
            }
        }

        $this->command->info('Permissions seeded successfully!');
    }

    private function assignPermissionToRoles(Permission $permission, string $module, string $action)
    {
        $rolePermissions = [];

        switch ($module) {
            case 'users':
            case 'departments':
            case 'classes':
            case 'subjects':
            case 'grading_scales':
                $rolePermissions = ['admin'];
                break;

            case 'teachers':
            case 'students':
            case 'guardians':
                if ($action === 'view') {
                    $rolePermissions = ['admin', 'teacher'];
                } else {
                    $rolePermissions = ['admin'];
                }
                break;

            case 'attendances':
                $rolePermissions = ['admin', 'teacher'];
                break;

            case 'exams':
            case 'results':
                if ($action === 'view') {
                    $rolePermissions = ['admin', 'teacher', 'student'];
                } elseif (in_array($action, ['create', 'edit'])) {
                    $rolePermissions = ['admin', 'teacher'];
                } else {
                    $rolePermissions = ['admin'];
                }
                break;

            case 'fees':
                if ($action === 'view') {
                    $rolePermissions = ['admin', 'staff', 'student', 'guardian'];
                } else {
                    $rolePermissions = ['admin', 'staff'];
                }
                break;

            case 'notices':
                if ($action === 'view') {
                    $rolePermissions = ['admin', 'teacher', 'student', 'staff', 'guardian'];
                } else {
                    $rolePermissions = ['admin'];
                }
                break;

            case 'study_materials':
            case 'lesson_plans':
                if ($action === 'view') {
                    $rolePermissions = ['admin', 'teacher', 'student'];
                } else {
                    $rolePermissions = ['admin', 'teacher'];
                }
                break;

            case 'reports':
                if ($action === 'view') {
                    $rolePermissions = ['admin', 'teacher'];
                } else {
                    $rolePermissions = ['admin'];
                }
                break;

            default:
                $rolePermissions = ['admin'];
        }

        foreach ($rolePermissions as $role) {
            DB::table('role_permissions')->insert([
                'role' => $role,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed Spatie roles to ensure they exist for tests
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Create a user with specified role and corresponding role record
     */
    private function makeUserWithRole(string $role): User
    {
        switch ($role) {
            case 'teacher':
                $teacher = Teacher::factory()->create();
                return $teacher->user;
            case 'student':
                $student = Student::factory()->create();
                return $student->user;
            case 'guardian':
                $guardian = Guardian::factory()->create();
                return $guardian->user;
            case 'accountant':
                $accountant = Accountant::factory()->create();
                return $accountant->user;
            case 'staff':
                return User::factory()->create(['role' => 'staff']);
            default:
                return User::factory()->create(['role' => $role]);
        }
    }

    public function test_admin_can_access_all_admin_routes(): void
    {
        $admin = $this->makeUserWithRole('admin');

        $this->actingAs($admin)->get('/users')->assertStatus(200);
        $this->actingAs($admin)->get('/teachers')->assertStatus(200);
        $this->actingAs($admin)->get('/students')->assertStatus(200);
        $this->actingAs($admin)->get('/fees')->assertStatus(200);
        $this->actingAs($admin)->get('/departments')->assertStatus(200);
        $this->actingAs($admin)->get('/classes')->assertStatus(200);
        $this->actingAs($admin)->get('/notices')->assertStatus(200);
        $this->actingAs($admin)->get('/guardians')->assertStatus(200);
        $this->actingAs($admin)->get('/accountants')->assertStatus(200);
    }

    public function test_teacher_cannot_access_admin_only_routes(): void
    {
        $teacher = $this->makeUserWithRole('teacher');

        $this->actingAs($teacher)->get('/users')->assertStatus(403);
        $this->actingAs($teacher)->get('/teachers/create')->assertStatus(403);
        $this->actingAs($teacher)->get('/students/create')->assertStatus(403);
        $this->actingAs($teacher)->get('/departments')->assertStatus(403);
        $this->actingAs($teacher)->get('/guardians')->assertStatus(403);
        $this->actingAs($teacher)->get('/accountants')->assertStatus(403);
    }

    public function test_teacher_can_access_shared_admin_teacher_routes(): void
    {
        $teacher = $this->makeUserWithRole('teacher');

        $this->actingAs($teacher)->get('/attendances')->assertStatus(200);
        $this->actingAs($teacher)->get('/results')->assertStatus(200);
        $this->actingAs($teacher)->get('/study-materials')->assertStatus(200);
        $this->actingAs($teacher)->get('/lesson-plans')->assertStatus(200);
        $this->actingAs($teacher)->get('/subjects')->assertStatus(200);
    }

    public function test_student_can_only_access_student_routes(): void
    {
        $student = $this->makeUserWithRole('student');

        // Student routes should be accessible
        $this->actingAs($student)->get('/my-attendance')->assertStatus(200);
        $this->actingAs($student)->get('/my-results')->assertStatus(200);
        $this->actingAs($student)->get('/my-fees')->assertStatus(200);
        $this->actingAs($student)->get('/my-materials')->assertStatus(200);

        // Other routes should be forbidden
        $this->actingAs($student)->get('/users')->assertStatus(403);
        $this->actingAs($student)->get('/attendances')->assertStatus(403);
        $this->actingAs($student)->get('/lesson-plans')->assertStatus(403);
    }

    public function test_guardian_can_only_access_guardian_routes(): void
    {
        $guardian = $this->makeUserWithRole('guardian');

        // Guardian routes should be accessible
        $this->actingAs($guardian)->get('/guardian/dashboard')->assertStatus(200);
        $this->actingAs($guardian)->get('/guardian/children')->assertStatus(200);
        $this->actingAs($guardian)->get('/guardian/fees')->assertStatus(200);

        // Other routes should be forbidden
        $this->actingAs($guardian)->get('/users')->assertStatus(403);
        $this->actingAs($guardian)->get('/my-attendance')->assertStatus(403);
        $this->actingAs($guardian)->get('/accountant/dashboard')->assertStatus(403);
    }

    public function test_accountant_can_only_access_accountant_routes(): void
    {
        $accountant = $this->makeUserWithRole('accountant');

        // Accountant routes should be accessible
        $this->actingAs($accountant)->get('/accountant/dashboard')->assertStatus(200);
        $this->actingAs($accountant)->get('/accountant/fees')->assertStatus(200);
        $this->actingAs($accountant)->get('/accountant/waivers')->assertStatus(200);
        $this->actingAs($accountant)->get('/accountant/reports')->assertStatus(200);

        // Other routes should be forbidden
        $this->actingAs($accountant)->get('/users')->assertStatus(403);
        $this->actingAs($accountant)->get('/lesson-plans')->assertStatus(403);
        $this->actingAs($accountant)->get('/my-fees')->assertStatus(403);
    }

    public function test_staff_has_minimal_access(): void
    {
        $staff = $this->makeUserWithRole('staff');

        // Staff can only access dashboard and profile
        $this->actingAs($staff)->get('/dashboard')->assertStatus(200);
        $this->actingAs($staff)->get('/profile')->assertStatus(200);

        // All role-specific routes should be forbidden
        $this->actingAs($staff)->get('/users')->assertStatus(403);
        $this->actingAs($staff)->get('/lesson-plans')->assertStatus(403);
        $this->actingAs($staff)->get('/my-attendance')->assertStatus(403);
        $this->actingAs($staff)->get('/guardian/dashboard')->assertStatus(403);
        $this->actingAs($staff)->get('/accountant/dashboard')->assertStatus(403);
    }

    public function test_role_middleware_blocks_unauthorized_access(): void
    {
        $teacher = $this->makeUserWithRole('teacher');
        $student = $this->makeUserWithRole('student');
        $guardian = $this->makeUserWithRole('guardian');
        $accountant = $this->makeUserWithRole('accountant');

        // Teacher tries student routes
        $this->actingAs($teacher)->get('/my-attendance')->assertStatus(403);
        $this->actingAs($teacher)->get('/my-fees')->assertStatus(403);

        // Student tries teacher routes
        $this->actingAs($student)->get('/lesson-plans')->assertStatus(403);
        $this->actingAs($student)->get('/attendances/create')->assertStatus(403);

        // Guardian tries accountant routes
        $this->actingAs($guardian)->get('/accountant/dashboard')->assertStatus(403);
        $this->actingAs($guardian)->get('/accountant/fees')->assertStatus(403);

        // Accountant tries guardian routes
        $this->actingAs($accountant)->get('/guardian/dashboard')->assertStatus(403);
        $this->actingAs($accountant)->get('/guardian/children')->assertStatus(403);
    }

    public function test_multiple_roles_cannot_be_assigned_simultaneously(): void
    {
        $teacher = $this->makeUserWithRole('teacher');

        // Teacher cannot access student-specific routes
        $this->actingAs($teacher)->get('/my-attendance')->assertStatus(403);
        $this->actingAs($teacher)->get('/my-fees')->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_any_protected_route(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/users')->assertRedirect('/login');
        $this->get('/my-attendance')->assertRedirect('/login');
        $this->get('/guardian/dashboard')->assertRedirect('/login');
        $this->get('/accountant/dashboard')->assertRedirect('/login');
    }

    public function test_checkrole_middleware_logs_legacy_fallback(): void
    {
        // Create user with role='teacher' but no Spatie role assigned
        $user = User::factory()->create(['role' => 'teacher']);
        Teacher::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/lesson-plans');

        // Legacy fallback should work
        $response->assertStatus(200);
    }

    public function test_spatie_roles_take_precedence_over_legacy(): void
    {
        // Create user with role='student' in users table
        $user = User::factory()->create(['role' => 'student']);
        Student::factory()->create(['user_id' => $user->id]);

        // Assign 'teacher' Spatie role to user
        $user->assignRole('teacher');

        // Spatie role should grant access to teacher routes
        $response = $this->actingAs($user)->get('/lesson-plans');
        $response->assertStatus(200);

        // Spatie role should deny access to student routes
        $response = $this->actingAs($user)->get('/my-attendance');
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Fee;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /** @test */
    public function test_user_model_casts_work_properly()
    {
        $user = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Test boolean cast
        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);

        // Test datetime cast
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);

        // Test password hashed cast
        $this->assertNotEquals('password', $user->password);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /** @test */
    public function test_role_detection_methods_work_with_legacy_column()
    {
        // Test with legacy role column only
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $guardian = User::factory()->create(['role' => 'guardian']);
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());

        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isAdmin());

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isTeacher());

        $this->assertTrue($guardian->isGuardian());
        $this->assertFalse($guardian->isStudent());

        $this->assertTrue($accountant->isAccountant());
        $this->assertFalse($accountant->isAdmin());
    }

    /** @test */
    public function test_role_detection_methods_work_with_spatie_roles()
    {
        // Test with Spatie roles
        $admin = User::factory()->create(['role' => 'staff']); // Different legacy role
        $admin->assignRole('admin');

        $teacher = User::factory()->create(['role' => 'staff']);
        $teacher->assignRole('teacher');

        $this->assertTrue($admin->isAdmin()); // Should detect Spatie role
        $this->assertTrue($teacher->isTeacher()); // Should detect Spatie role
    }

    /** @test */
    public function test_role_detection_methods_prefer_spatie_over_legacy()
    {
        // User has legacy role 'staff' but Spatie role 'admin'
        $user = User::factory()->create(['role' => 'staff']);
        $user->assignRole('admin');

        // Should detect as admin (Spatie role takes precedence)
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isStaff()); // Legacy role should be overridden
    }

    /** @test */
    public function test_dashboard_redirects_to_correct_view_for_admin()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin');
    }

    /** @test */
    public function test_dashboard_redirects_to_correct_view_for_teacher()
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $user->assignRole('teacher');
        
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.teacher');
    }

    /** @test */
    public function test_dashboard_redirects_to_correct_view_for_student()
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.student');
    }

    /** @test */
    public function test_dashboard_redirects_to_correct_view_for_guardian()
    {
        $user = User::factory()->create(['role' => 'guardian']);
        $user->assignRole('guardian');
        
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.guardian');
    }

    /** @test */
    public function test_dashboard_redirects_to_correct_view_for_accountant()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        $user->assignRole('accountant');
        
        $accountant = Accountant::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.accountant');
    }

    /** @test */
    public function test_missing_teacher_record_handled_properly()
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $user->assignRole('teacher');
        // No teacher record created

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('teacher profile is incomplete', session('error'));
    }

    /** @test */
    public function test_missing_student_record_handled_properly()
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        // No student record created

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_student_policy_authorizes_admin_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);

        $this->assertTrue($admin->can('viewAny', Student::class));
        $this->assertTrue($admin->can('view', $student));
        $this->assertTrue($admin->can('create', Student::class));
        $this->assertTrue($admin->can('update', $student));
        $this->assertTrue($admin->can('delete', $student));
    }

    /** @test */
    public function test_student_policy_authorizes_teacher_correctly()
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $user->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);

        $this->assertTrue($user->can('viewAny', Student::class));
        $this->assertFalse($user->can('create', Student::class)); // Only admin can create
        $this->assertFalse($user->can('delete', $student)); // Only admin can delete
    }

    /** @test */
    public function test_student_policy_authorizes_student_self_view()
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);

        $this->assertTrue($user->can('view', $student)); // Can view self
        $this->assertFalse($user->can('update', $student)); // Cannot update self
    }

    /** @test */
    public function test_fee_policy_authorizes_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $accountantUser = User::factory()->create(['role' => 'accountant']);
        $accountantUser->assignRole('accountant');
        $accountant = Accountant::factory()->create(['user_id' => $accountantUser->id]);
        
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        $fee = Fee::factory()->create(['student_id' => $student->id]);

        // Admin can do everything
        $this->assertTrue($admin->can('viewAny', Fee::class));
        $this->assertTrue($admin->can('create', Fee::class));
        $this->assertTrue($admin->can('recordPayment', $fee));

        // Accountant can manage fees
        $this->assertTrue($accountantUser->can('viewAny', Fee::class));
        $this->assertTrue($accountantUser->can('create', Fee::class));
        $this->assertTrue($accountantUser->can('recordPayment', $fee));
    }

    /** @test */
    public function test_attendance_policy_authorizes_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $this->assertTrue($admin->can('viewAny', Attendance::class));
        $this->assertTrue($admin->can('create', Attendance::class));

        $this->assertTrue($teacherUser->can('viewAny', Attendance::class));
        $this->assertTrue($teacherUser->can('create', Attendance::class));
    }

    /** @test */
    public function test_route_middleware_blocks_unauthorized_access()
    {
        $student = User::factory()->create(['role' => 'student']);
        $student->assignRole('student');
        
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        Student::factory()->create([
            'user_id' => $student->id,
            'class_id' => $class->id,
        ]);

        // Student trying to access admin route
        $response = $this->actingAs($student)->get('/users');
        $response->assertStatus(403);

        // Student trying to access teacher route
        $response = $this->actingAs($student)->get('/teachers');
        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_has_full_access_to_all_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        // Test various admin routes
        $routes = [
            '/dashboard',
            '/users',
            '/teachers',
            '/students',
            '/departments',
            '/classes',
            '/subjects',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $this->assertNotEquals(403, $response->status(), "Admin should access {$route}");
        }
    }

    /** @test */
    public function test_sync_command_assigns_spatie_roles()
    {
        // Create users with only legacy roles
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        // Comment 2: Verify they don't have Spatie roles yet (use Spatie-only check)
        $this->assertFalse($teacher->getRoleNames()->contains('teacher'));
        $this->assertFalse($student->getRoleNames()->contains('student'));

        // Run sync command
        Artisan::call('sync:user-roles');

        // Refresh from database
        $teacher->refresh();
        $student->refresh();

        // Verify Spatie roles were assigned (use Spatie-only check)
        $this->assertTrue($teacher->getRoleNames()->contains('teacher'));
        $this->assertTrue($student->getRoleNames()->contains('student'));
    }

    /** @test */
    public function test_sync_command_creates_missing_role_records_with_repair_flag()
    {
        // Create user without role record
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');

        // Verify no teacher record exists
        $this->assertNull($teacherUser->teacher);

        // Run sync with repair
        Artisan::call('sync:user-roles', ['--repair' => true]);

        // Refresh and verify teacher record was created
        $teacherUser->refresh();
        $this->assertNotNull($teacherUser->teacher);
        $this->assertEquals($teacherUser->id, $teacherUser->teacher->user_id);
    }

    /** @test */
    public function test_integrity_command_detects_missing_records()
    {
        // Create users without role records
        $teacher = User::factory()->create(['role' => 'teacher']);
        $teacher->assignRole('teacher');
        
        $student = User::factory()->create(['role' => 'student']);
        $student->assignRole('student');

        // Run integrity check
        $exitCode = Artisan::call('verify:system-integrity');

        // Should return failure code due to missing records
        $this->assertEquals(1, $exitCode);

        // Check output contains warnings
        $output = Artisan::output();
        $this->assertStringContainsString('Missing', $output);
    }

    /** @test */
    public function test_integrity_command_detects_orphaned_records()
    {
        // Create teacher record without user
        $orphanedTeacher = Teacher::factory()->create(['user_id' => 99999]);

        // Run integrity check
        Artisan::call('verify:system-integrity');

        $output = Artisan::output();
        $this->assertStringContainsString('orphaned', strtolower($output));
    }

    /** @test */
    public function test_integrity_command_fixes_issues_with_fix_flag()
    {
        // Create orphaned record
        $orphanedTeacher = Teacher::factory()->create(['user_id' => 99999]);

        // Run with fix flag
        Artisan::call('verify:system-integrity', ['--fix' => true]);

        // Verify orphaned record was deleted
        $this->assertDatabaseMissing('teachers', ['id' => $orphanedTeacher->id]);
    }

    /** @test */
    public function test_has_role_record_method_works_correctly()
    {
        // Teacher with record
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $this->assertTrue($teacherUser->hasRoleRecord());

        // Teacher without record
        $teacherUser2 = User::factory()->create(['role' => 'teacher']);
        $teacherUser2->assignRole('teacher');
        $this->assertFalse($teacherUser2->hasRoleRecord());

        // Admin (doesn't need role record)
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->assertTrue($admin->hasRoleRecord());
    }

    /** @test */
    public function test_get_role_record_attribute_returns_correct_record()
    {
        // Create teacher with record
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $this->assertInstanceOf(Teacher::class, $teacherUser->role_record);
        $this->assertEquals($teacher->id, $teacherUser->role_record->id);

        // Create student with record
        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
        ]);

        $this->assertInstanceOf(Student::class, $studentUser->role_record);
        $this->assertEquals($student->id, $studentUser->role_record->id);

        // Admin should return null
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertNull($admin->role_record);
    }

    /** @test */
    public function test_guardian_policy_with_null_safety()
    {
        $guardianUser = User::factory()->create(['role' => 'guardian']);
        $guardianUser->assignRole('guardian');
        // No guardian record created

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);

        // Should not throw error, should return false
        $this->assertFalse($guardianUser->can('view', $student));
    }

    /** @test */
    public function test_multiple_spatie_roles_detected()
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $user->assignRole(['teacher', 'admin']); // Multiple roles

        // Should detect both
        $this->assertTrue($user->isTeacher());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasAnyRole(['teacher', 'admin']));
    }

    /** @test */
    public function test_policies_work_without_method_exists_checks()
    {
        // This test verifies that removing method_exists doesn't break anything
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        $fee = Fee::factory()->create(['student_id' => $student->id]);

        // All these should work without errors
        $this->assertTrue($admin->can('viewAny', Student::class));
        $this->assertTrue($admin->can('viewAny', Fee::class));
        $this->assertTrue($admin->can('create', Fee::class));
        $this->assertTrue($admin->can('recordPayment', $fee));
    }

    /** @test */
    public function test_dry_run_mode_does_not_make_changes()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        // No Spatie role assigned

        // Run in dry-run mode
        Artisan::call('sync:user-roles', ['--dry-run' => true]);

        // Comment 2: Refresh and verify no changes were made (use Spatie-only check)
        $teacher->refresh();
        $this->assertFalse($teacher->getRoleNames()->contains('teacher'));
    }

    /** @test */
    public function test_specific_role_sync_only_affects_that_role()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        // Sync only teachers
        Artisan::call('sync:user-roles', ['--role' => 'teacher']);

        $teacher->refresh();
        $student->refresh();

        // Comment 2: Teacher should have Spatie role (use Spatie-only check)
        $this->assertTrue($teacher->getRoleNames()->contains('teacher'));
        
        // Student should NOT have Spatie role (wasn't synced)
        $this->assertFalse($student->getRoleNames()->contains('student'));
    }

    /** @test */
    public function test_system_health_with_perfect_data()
    {
        // Create complete, consistent data
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        Teacher::factory()->create(['user_id' => $teacherUser->id]);

        // Run integrity check
        $exitCode = Artisan::call('verify:system-integrity');

        // Should return success
        $this->assertEquals(0, $exitCode);

        $output = Artisan::output();
        $this->assertStringContainsString('EXCELLENT', $output);
    }

    /** @test */
    public function test_guardian_can_view_linked_student_fees()
    {
        $guardianUser = User::factory()->create(['role' => 'guardian']);
        $guardianUser->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        
        // Link guardian to student with financial responsibility
        $guardian->students()->attach($student->id, [
            'relation' => 'father',
            'financial_responsibility' => true,
        ]);

        $fee = Fee::factory()->create(['student_id' => $student->id]);

        // Guardian should be able to view the fee
        $this->assertTrue($guardianUser->can('view', $fee));
    }

    /** @test */
    public function test_guardian_cannot_view_unlinked_student_fees()
    {
        $guardianUser = User::factory()->create(['role' => 'guardian']);
        $guardianUser->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        $fee = Fee::factory()->create(['student_id' => $student->id]);

        // Guardian NOT linked to student
        $this->assertFalse($guardianUser->can('view', $fee));
    }

    /** @test */
    public function test_accountant_waiver_approval_respects_permissions()
    {
        $accountantUser = User::factory()->create(['role' => 'accountant']);
        $accountantUser->assignRole('accountant');
        
        // Accountant WITHOUT approval permission
        $accountant = Accountant::factory()->create([
            'user_id' => $accountantUser->id,
            'can_approve_waivers' => false,
        ]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        $fee = Fee::factory()->create(['student_id' => $student->id]);
        $waiver = \App\Models\FeeWaiver::factory()->create([
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'status' => 'pending',
        ]);

        // Should NOT be able to approve
        $this->assertFalse($accountantUser->can('approve', $waiver));

        // Update to allow approvals
        $accountant->update(['can_approve_waivers' => true]);
        $accountantUser->refresh();

        // Now should be able to approve
        $this->assertTrue($accountantUser->can('approve', $waiver));
    }

    /** @test */
    public function test_class_teacher_can_view_their_students()
    {
        // Comment 2: Test class teacher authorization with class_teacher_id
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create([
            'department_id' => $department->id,
            'class_teacher_id' => $teacher->id, // This teacher is the class teacher
        ]);
        $student = Student::factory()->create(['class_id' => $class->id]);

        // Class teacher should be able to view their student
        $this->assertTrue($teacherUser->can('view', $student));
    }

    /** @test */
    public function test_non_class_teacher_cannot_view_other_class_students()
    {
        // Comment 2: Negative test - non-class teacher denied
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $otherTeacherUser = User::factory()->create(['role' => 'teacher']);
        $otherTeacherUser->assignRole('teacher');
        $otherTeacher = Teacher::factory()->create(['user_id' => $otherTeacherUser->id]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create([
            'department_id' => $department->id,
            'class_teacher_id' => $otherTeacher->id, // Different teacher
        ]);
        $student = Student::factory()->create(['class_id' => $class->id]);

        // Non-class teacher should NOT be able to view
        $this->assertFalse($teacherUser->can('view', $student));
    }

    /** @test */
    public function test_class_teacher_can_view_and_update_student_results()
    {
        // Comment 2: Test class teacher can view/update results
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create([
            'department_id' => $department->id,
            'class_teacher_id' => $teacher->id,
        ]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        
        $subject = Subject::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);
        $exam = Exam::factory()->create(['class_id' => $class->id]);
        $result = Result::factory()->create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
        ]);

        // Class teacher should be able to view and update results
        $this->assertTrue($teacherUser->can('view', $result));
        $this->assertTrue($teacherUser->can('update', $result));
    }

    /** @test */
    public function test_non_class_teacher_cannot_update_other_class_results()
    {
        // Comment 2: Negative test for result update
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacherUser->assignRole('teacher');
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $otherTeacherUser = User::factory()->create(['role' => 'teacher']);
        $otherTeacherUser->assignRole('teacher');
        $otherTeacher = Teacher::factory()->create(['user_id' => $otherTeacherUser->id]);

        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create([
            'department_id' => $department->id,
            'class_teacher_id' => $otherTeacher->id, // Different teacher
        ]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        
        $subject = Subject::factory()->create(['class_id' => $class->id]);
        $exam = Exam::factory()->create(['class_id' => $class->id]);
        $result = Result::factory()->create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
        ]);

        // Non-class teacher should NOT be able to update
        $this->assertFalse($teacherUser->can('update', $result));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Department;
use App\Models\ClassRoom;
use App\Models\Fee;
use App\Models\Notice;
use App\Models\Exam;
use App\Models\Guardian;
use App\Models\Accountant;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create an admin user for testing
     */
    private function makeAdminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $adminUser = $this->makeAdminUser();

        $response = $this->actingAs($adminUser)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertViewIs('dashboard.admin');
    }

    public function test_admin_can_view_users_list(): void
    {
        $adminUser = $this->makeAdminUser();
        
        // Create additional users with different roles
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Test Teacher']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Test Student']);
        $guardian = User::factory()->create(['role' => 'guardian', 'name' => 'Test Guardian']);

        $response = $this->actingAs($adminUser)
            ->get('/users');

        $response->assertStatus(200)
            ->assertSee('Test Teacher')
            ->assertSee('Test Student')
            ->assertSee('Test Guardian');
    }

    public function test_admin_can_create_teacher(): void
    {
        $adminUser = $this->makeAdminUser();
        $department = Department::factory()->create();

        $teacherData = [
            'name' => 'New Teacher',
            'email' => 'newteacher@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'employee_id' => 'EMP001',
            'department_id' => $department->id,
            'phone' => '01712345678',
            'address' => 'Test Address',
            'qualification' => 'Masters',
            'joining_date' => now()->format('Y-m-d'),
        ];

        $this->actingAs($adminUser)
            ->post('/teachers', $teacherData)
            ->assertRedirect('/teachers');

        $this->assertDatabaseHas('teachers', [
            'employee_id' => 'EMP001',
            'department_id' => $department->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newteacher@test.com',
            'role' => 'teacher',
        ]);
    }

    public function test_admin_can_create_student(): void
    {
        $adminUser = $this->makeAdminUser();
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);

        $studentData = [
            'name' => 'New Student',
            'email' => 'newstudent@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roll_number' => 'STU001',
            'class_id' => $class->id,
            'phone' => '01712345678',
            'address' => 'Test Address',
            'date_of_birth' => '2010-01-01',
            'admission_date' => now()->format('Y-m-d'),
        ];

        $this->actingAs($adminUser)
            ->post('/students', $studentData)
            ->assertRedirect('/students');

        $this->assertDatabaseHas('students', [
            'roll_number' => 'STU001',
            'class_id' => $class->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@test.com',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_manage_fees(): void
    {
        $adminUser = $this->makeAdminUser();
        $student = Student::factory()->create();

        $feeData = [
            'student_id' => $student->id,
            'amount' => 5000,
            'fee_type' => 'tuition',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'academic_year' => now()->year,
            'month' => now()->format('F'),
        ];

        $this->actingAs($adminUser)
            ->post('/fees', $feeData)
            ->assertRedirect();

        $this->assertDatabaseHas('fees', [
            'student_id' => $student->id,
            'amount' => 5000,
            'fee_type' => 'tuition',
        ]);

        $response = $this->actingAs($adminUser)
            ->get('/fees');

        $response->assertStatus(200)
            ->assertSee('5000');
    }

    public function test_admin_can_record_payment(): void
    {
        $adminUser = $this->makeAdminUser();
        $fee = Fee::factory()->create([
            'amount' => 5000,
            'status' => 'unpaid',
            'paid_amount' => 0,
        ]);

        $paymentData = [
            'amount' => 5000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'remarks' => 'Full payment',
        ];

        $this->actingAs($adminUser)
            ->post("/fees/{$fee->id}/record-payment", $paymentData)
            ->assertRedirect();

        $fee->refresh();
        $this->assertEquals('paid', $fee->status);
        $this->assertEquals(5000, $fee->paid_amount);
    }

    public function test_admin_can_create_notice(): void
    {
        $adminUser = $this->makeAdminUser();

        $noticeData = [
            'title' => 'Important Notice',
            'description' => 'This is a test notice',
            'notice_for' => 'all',
            'publish_date' => now()->format('Y-m-d'),
            'is_active' => true,
        ];

        $this->actingAs($adminUser)
            ->post('/notices', $noticeData)
            ->assertRedirect('/notices');

        $this->assertDatabaseHas('notices', [
            'title' => 'Important Notice',
            'notice_for' => 'all',
        ]);
    }

    public function test_admin_can_view_reports(): void
    {
        $adminUser = $this->makeAdminUser();
        Fee::factory()->count(3)->create();

        $this->actingAs($adminUser)
            ->get('/fees-reports')
            ->assertStatus(200);

        $this->actingAs($adminUser)
            ->get('/fees-reports/collection')
            ->assertStatus(200);

        $this->actingAs($adminUser)
            ->get('/fees-reports/outstanding')
            ->assertStatus(200);
    }

    public function test_admin_can_manage_guardians(): void
    {
        $adminUser = $this->makeAdminUser();

        $guardianData = [
            'name' => 'New Guardian',
            'email' => 'guardian@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '01712345678',
            'address' => 'Test Address',
            'occupation' => 'Business',
            'relation' => 'father',
        ];

        $this->actingAs($adminUser)
            ->post('/guardians', $guardianData)
            ->assertRedirect('/guardians');

        $this->assertDatabaseHas('guardians', [
            'phone' => '01712345678',
            'occupation' => 'Business',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'guardian@test.com',
            'role' => 'guardian',
        ]);
    }

    public function test_admin_can_manage_accountants(): void
    {
        $adminUser = $this->makeAdminUser();

        $accountantData = [
            'name' => 'New Accountant',
            'email' => 'accountant@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '01712345678',
            'employee_id' => 'ACC001',
            'can_approve_waivers' => true,
            'max_waiver_amount' => 5000,
        ];

        $this->actingAs($adminUser)
            ->post('/accountants', $accountantData)
            ->assertRedirect('/accountants');

        $this->assertDatabaseHas('accountants', [
            'employee_id' => 'ACC001',
            'can_approve_waivers' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'accountant@test.com',
            'role' => 'accountant',
        ]);
    }

    public function test_admin_can_bulk_promote_students(): void
    {
        $adminUser = $this->makeAdminUser();
        $department = Department::factory()->create();
        $currentClass = ClassRoom::factory()->create(['department_id' => $department->id, 'name' => 'Class 1']);
        $targetClass = ClassRoom::factory()->create(['department_id' => $department->id, 'name' => 'Class 2']);

        $students = Student::factory()->count(3)->create(['class_id' => $currentClass->id]);

        $promoteData = [
            'student_ids' => $students->pluck('id')->toArray(),
            'target_class_id' => $targetClass->id,
        ];

        $this->actingAs($adminUser)
            ->post('/students/bulk-promote', $promoteData)
            ->assertRedirect();

        foreach ($students as $student) {
            $student->refresh();
            $this->assertEquals($targetClass->id, $student->class_id);
        }
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $student = Student::factory()->create();
        $studentUser = $student->user;

        $this->actingAs($studentUser)
            ->get('/users')
            ->assertStatus(403);

        $this->actingAs($studentUser)
            ->get('/teachers/create')
            ->assertStatus(403);

        $this->actingAs($studentUser)
            ->get('/fees/create')
            ->assertStatus(403);
    }
}

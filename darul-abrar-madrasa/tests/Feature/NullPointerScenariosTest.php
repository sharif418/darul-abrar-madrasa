<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use App\Models\LessonPlan;
use Illuminate\Support\Facades\Log;

class NullPointerScenariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_without_teacher_record_sees_error_on_dashboard(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
        
        $this->assertStringContainsString('teacher profile is incomplete', session('error'));
        $this->assertStringContainsString('contact the administrator', session('error'));
    }

    public function test_teacher_without_teacher_record_error_is_logged(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) use ($user) {
                return str_contains($message, 'Teacher record missing for user') &&
                       str_contains($message, (string) $user->id) &&
                       str_contains($message, $user->email);
            });

        $this->actingAs($user)->get('/dashboard');
    }

    public function test_student_without_student_record_sees_error_on_dashboard(): void
    {
        // Create user with role='student' but NO student record
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
        
        $this->assertStringContainsString('student profile is incomplete', session('error'));
    }

    public function test_student_without_student_record_error_is_logged(): void
    {
        // Create user with role='student' but NO student record
        $user = User::factory()->create(['role' => 'student']);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) use ($user) {
                return str_contains($message, 'Student record missing for user');
            });

        $this->actingAs($user)->get('/dashboard');
    }

    public function test_guardian_without_guardian_record_sees_error_on_dashboard(): void
    {
        // Create user with role='guardian' but NO guardian record
        $user = User::factory()->create(['role' => 'guardian']);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
        
        $this->assertStringContainsString('guardian profile is incomplete', session('error'));
    }

    public function test_guardian_without_guardian_record_error_is_logged(): void
    {
        // Create user with role='guardian' but NO guardian record
        $user = User::factory()->create(['role' => 'guardian']);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) use ($user) {
                return str_contains($message, 'Guardian record missing for user');
            });

        $this->actingAs($user)->get('/dashboard');
    }

    public function test_accountant_without_accountant_record_sees_error_on_dashboard(): void
    {
        // Create user with role='accountant' but NO accountant record
        $user = User::factory()->create(['role' => 'accountant']);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
        
        $this->assertStringContainsString('accountant profile is incomplete', session('error'));
    }

    public function test_accountant_without_accountant_record_error_is_logged(): void
    {
        // Create user with role='accountant' but NO accountant record
        $user = User::factory()->create(['role' => 'accountant']);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) use ($user) {
                return str_contains($message, 'Accountant record missing for user');
            });

        $this->actingAs($user)->get('/dashboard');
    }

    public function test_teacher_without_record_cannot_access_lesson_plans(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($user)
            ->get('/lesson-plans');

        $response->assertRedirect('/dashboard')
            ->assertSessionHas('error');
    }

    public function test_teacher_without_record_cannot_create_lesson_plan(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $lessonPlanData = [
            'topic' => 'Test Topic',
            'date' => now()->format('Y-m-d'),
            'objectives' => 'Test objectives',
        ];

        $response = $this->actingAs($user)
            ->post('/lesson-plans', $lessonPlanData);

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('lesson_plans', [
            'topic' => 'Test Topic',
        ]);
    }

    public function test_teacher_without_record_cannot_access_subjects(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($user)
            ->get('/subjects');

        // Should handle null teacher gracefully
        $response->assertStatus(200);
    }

    public function test_student_without_record_cannot_access_my_attendance(): void
    {
        // Create user with role='student' but NO student record
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)
            ->get('/my-attendance');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
    }

    public function test_student_without_record_cannot_access_my_results(): void
    {
        // Create user with role='student' but NO student record
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)
            ->get('/my-results');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
    }

    public function test_student_without_record_cannot_access_my_fees(): void
    {
        // Create user with role='student' but NO student record
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)
            ->get('/my-fees');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
    }

    public function test_guardian_without_record_cannot_access_children(): void
    {
        // Create user with role='guardian' but NO guardian record
        $user = User::factory()->create(['role' => 'guardian']);

        $response = $this->actingAs($user)
            ->get('/guardian/children');

        $response->assertStatus(403);
    }

    public function test_accountant_without_record_cannot_access_fees(): void
    {
        // Create user with role='accountant' but NO accountant record
        $user = User::factory()->create(['role' => 'accountant']);

        $response = $this->actingAs($user)
            ->get('/accountant/fees');

        $response->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_user_hasRoleRecord_method_returns_false_for_missing_records(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $this->assertFalse($user->hasRoleRecord());

        // Create teacher record for user
        Teacher::factory()->create(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($user->hasRoleRecord());
    }

    public function test_verify_role_records_command_detects_missing_records(): void
    {
        // Create 2 users with role='teacher' but only 1 teacher record
        $user1 = User::factory()->create(['role' => 'teacher']);
        $user2 = User::factory()->create(['role' => 'teacher']);
        Teacher::factory()->create(['user_id' => $user1->id]);

        $this->artisan('verify:role-records')
            ->expectsOutput('1 missing')
            ->assertExitCode(0);
    }

    public function test_verify_role_records_repair_creates_missing_records(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $this->assertFalse($user->hasRoleRecord());

        $this->artisan('verify:role-records', ['--repair' => true, '--force' => true])
            ->assertExitCode(0);

        $user->refresh();
        $this->assertTrue($user->hasRoleRecord());

        // Verify teacher record exists with placeholder values
        $this->assertDatabaseHas('teachers', [
            'user_id' => $user->id,
        ]);
    }
}

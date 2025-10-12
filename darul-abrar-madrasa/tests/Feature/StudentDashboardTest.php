<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Attendance;
use App\Models\Result;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\StudyMaterial;
use App\Models\Notice;
use App\Models\Subject;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a student user with associated data
     */
    private function makeStudentWithData(): array
    {
        $class = ClassRoom::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        return [$student->user, $student, $class];
    }

    public function test_student_can_access_dashboard(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertViewIs('dashboard.student');
    }

    public function test_student_can_view_own_attendance(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        // Create attendance records (3 present, 2 absent)
        Attendance::factory()->count(3)->create([
            'student_id' => $student->id,
            'status' => 'present',
        ]);
        Attendance::factory()->count(2)->create([
            'student_id' => $student->id,
            'status' => 'absent',
        ]);

        $response = $this->actingAs($user)
            ->get('/my-attendance');

        $response->assertStatus(200);
        
        // Should see attendance records
        $this->assertEquals(5, Attendance::where('student_id', $student->id)->count());
    }

    public function test_student_cannot_view_other_student_attendance(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        // Create attendance for acting student with specific date
        Attendance::factory()->create([
            'student_id' => $student->id,
            'status' => 'present',
            'date' => '2025-01-01',
        ]);
        
        // Create another student with attendance on different date
        $otherStudent = Student::factory()->create();
        Attendance::factory()->create([
            'student_id' => $otherStudent->id,
            'status' => 'absent',
            'date' => '2025-01-02',
        ]);

        $response = $this->actingAs($user)
            ->get('/my-attendance');

        $response->assertStatus(200)
            ->assertSee('2025-01-01')
            ->assertDontSee('2025-01-02');
    }

    public function test_student_can_view_own_results(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $exam = Exam::factory()->create(['class_id' => $class->id]);
        $subjects = Subject::factory()->count(3)->create(['class_id' => $class->id]);
        
        foreach ($subjects as $subject) {
            Result::factory()->create([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
                'marks_obtained' => 85,
            ]);
        }

        $response = $this->actingAs($user)
            ->get('/my-results');

        $response->assertStatus(200)
            ->assertSee($exam->name);
    }

    public function test_student_can_download_mark_sheet(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $exam = Exam::factory()->create(['class_id' => $class->id]);
        $subject = Subject::factory()->create(['class_id' => $class->id]);
        
        Result::factory()->create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/results/{$exam->id}/{$student->id}/mark-sheet");

        $response->assertStatus(200);
    }

    public function test_student_cannot_view_other_student_results(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $otherStudent = Student::factory()->create();
        $exam = Exam::factory()->create();
        
        Result::factory()->create([
            'student_id' => $otherStudent->id,
            'exam_id' => $exam->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/results/{$exam->id}/{$otherStudent->id}/mark-sheet");

        $response->assertStatus(403);
    }

    public function test_student_can_view_own_fees(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        // Create fees with different statuses
        Fee::factory()->create([
            'student_id' => $student->id,
            'status' => 'paid',
            'amount' => 5000,
        ]);
        Fee::factory()->create([
            'student_id' => $student->id,
            'status' => 'unpaid',
            'amount' => 3000,
        ]);
        Fee::factory()->create([
            'student_id' => $student->id,
            'status' => 'partial',
            'amount' => 4000,
        ]);

        $response = $this->actingAs($user)
            ->get('/my-fees');

        $response->assertStatus(200)
            ->assertSee('5000')
            ->assertSee('3000')
            ->assertSee('4000');
    }

    public function test_student_cannot_view_other_student_fees(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $otherStudent = Student::factory()->create();
        Fee::factory()->create([
            'student_id' => $otherStudent->id,
            'amount' => 9999,
        ]);

        $response = $this->actingAs($user)
            ->get('/my-fees');

        $response->assertStatus(200)
            ->assertDontSee('9999');
    }

    public function test_student_can_view_study_materials(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $subject = Subject::factory()->create(['class_id' => $class->id]);
        
        // Create published materials for student's class
        $publishedMaterial1 = StudyMaterial::factory()->create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_published' => true,
            'title' => 'Published Material 1',
        ]);
        $publishedMaterial2 = StudyMaterial::factory()->create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_published' => true,
            'title' => 'Published Material 2',
        ]);
        
        // Create unpublished material (should not see)
        StudyMaterial::factory()->create([
            'class_id' => $class->id,
            'is_published' => false,
            'title' => 'Unpublished Material',
        ]);
        
        // Create material for different class (should not see)
        $otherClass = ClassRoom::factory()->create();
        StudyMaterial::factory()->create([
            'class_id' => $otherClass->id,
            'is_published' => true,
            'title' => 'Other Class Material',
        ]);

        $response = $this->actingAs($user)
            ->get('/my-materials');

        $response->assertStatus(200)
            ->assertSee('Published Material 1')
            ->assertSee('Published Material 2')
            ->assertDontSee('Unpublished Material')
            ->assertDontSee('Other Class Material');
    }

    public function test_student_can_download_study_material(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $subject = Subject::factory()->create(['class_id' => $class->id]);
        $material = StudyMaterial::factory()->create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_published' => true,
            'content_type' => 'document',
        ]);

        $response = $this->actingAs($user)
            ->get("/study-materials/{$material->id}/download");

        $response->assertStatus(200);
    }

    public function test_student_cannot_download_unpublished_material(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        $material = StudyMaterial::factory()->create([
            'class_id' => $class->id,
            'is_published' => false,
        ]);

        $response = $this->actingAs($user)
            ->get("/study-materials/{$material->id}/download");

        $response->assertStatus(403);
    }

    public function test_student_can_view_notices(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();
        
        // Create notices for students and all
        Notice::factory()->create([
            'notice_for' => 'students',
            'title' => 'Student Notice',
            'is_active' => true,
        ]);
        Notice::factory()->create([
            'notice_for' => 'all',
            'title' => 'All Notice',
            'is_active' => true,
        ]);
        
        // Create notice for teachers (should not see)
        Notice::factory()->create([
            'notice_for' => 'teachers',
            'title' => 'Teacher Notice',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get('/notices/public');

        $response->assertStatus(200)
            ->assertSee('Student Notice')
            ->assertSee('All Notice')
            ->assertDontSee('Teacher Notice');
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();

        $this->actingAs($user)
            ->get('/users')
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/fees/create')
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/teachers')
            ->assertStatus(403);
    }

    public function test_student_cannot_access_teacher_routes(): void
    {
        [$user, $student, $class] = $this->makeStudentWithData();

        $this->actingAs($user)
            ->get('/lesson-plans')
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/attendances/create')
            ->assertStatus(403);
    }

    public function test_student_with_missing_record_sees_error(): void
    {
        // Create user with role='student' but NO student record
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
        
        $this->assertStringContainsString('student profile is incomplete', session('error'));
    }
}

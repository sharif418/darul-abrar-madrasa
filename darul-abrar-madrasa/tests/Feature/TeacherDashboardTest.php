<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Result;
use App\Models\Exam;
use App\Models\LessonPlan;
use App\Models\StudyMaterial;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a teacher user with subjects
     */
    private function makeTeacherWithSubjects(): array
    {
        $department = Department::factory()->create();
        $class = ClassRoom::factory()->create(['department_id' => $department->id]);
        $teacher = Teacher::factory()->create(['department_id' => $department->id]);
        
        $subjects = Subject::factory()->count(2)->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
        ]);

        return [$teacher->user, $teacher, $subjects, $class];
    }

    public function test_teacher_can_access_dashboard(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertViewIs('dashboard.teacher');
    }

    public function test_teacher_can_view_assigned_subjects(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        
        // Create one more subject for this teacher
        $extraSubject = Subject::factory()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'name' => 'Extra Subject',
        ]);

        $response = $this->actingAs($user)
            ->get('/subjects');

        $response->assertStatus(200);
        
        foreach ($subjects as $subject) {
            $response->assertSee($subject->name);
        }
        $response->assertSee('Extra Subject');
    }

    public function test_teacher_can_create_lesson_plan(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        $subject = $subjects->first();

        $lessonPlanData = [
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'topic' => 'Introduction to Mathematics',
            'date' => now()->format('Y-m-d'),
            'objectives' => 'Learn basic concepts',
            'teaching_method' => 'Lecture and practice',
            'resources_required' => 'Textbook, whiteboard',
        ];

        $this->actingAs($user)
            ->post('/lesson-plans', $lessonPlanData)
            ->assertRedirect('/lesson-plans');

        $this->assertDatabaseHas('lesson_plans', [
            'teacher_id' => $teacher->id,
            'topic' => 'Introduction to Mathematics',
        ]);
    }

    public function test_teacher_can_view_own_lesson_plans(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        
        // Create lesson plans for this teacher
        $ownPlan1 = LessonPlan::factory()->create([
            'teacher_id' => $teacher->id,
            'topic' => 'Own Plan 1',
        ]);
        $ownPlan2 = LessonPlan::factory()->create([
            'teacher_id' => $teacher->id,
            'topic' => 'Own Plan 2',
        ]);

        // Create lesson plan for another teacher
        $otherTeacher = Teacher::factory()->create();
        $otherPlan = LessonPlan::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'topic' => 'Other Teacher Plan',
        ]);

        $response = $this->actingAs($user)
            ->get('/lesson-plans');

        $response->assertStatus(200)
            ->assertSee('Own Plan 1')
            ->assertSee('Own Plan 2')
            ->assertDontSee('Other Teacher Plan');
    }

    public function test_teacher_can_mark_lesson_plan_completed(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        
        $lessonPlan = LessonPlan::factory()->create([
            'teacher_id' => $teacher->id,
            'is_completed' => false,
        ]);

        $this->actingAs($user)
            ->post("/lesson-plans/{$lessonPlan->id}/mark-completed")
            ->assertRedirect();

        $lessonPlan->refresh();
        $this->assertTrue($lessonPlan->is_completed);
    }

    public function test_teacher_can_create_study_material(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        $subject = $subjects->first();

        $materialData = [
            'title' => 'Chapter 1 Notes',
            'description' => 'Important notes for chapter 1',
            'content_type' => 'note',
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'content' => 'This is the content of the notes',
            'is_published' => true,
        ];

        $this->actingAs($user)
            ->post('/study-materials', $materialData)
            ->assertRedirect('/study-materials');

        $this->assertDatabaseHas('study_materials', [
            'teacher_id' => $teacher->id,
            'title' => 'Chapter 1 Notes',
        ]);
    }

    public function test_teacher_can_toggle_study_material_published(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        
        $material = StudyMaterial::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
        ]);

        $this->actingAs($user)
            ->patch("/study-materials/{$material->id}/toggle-published")
            ->assertRedirect();

        $material->refresh();
        $this->assertFalse($material->is_published);
    }

    public function test_teacher_can_record_attendance(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        
        $students = Student::factory()->count(3)->create(['class_id' => $class->id]);

        $attendanceData = [
            'class_id' => $class->id,
            'date' => now()->format('Y-m-d'),
            'attendance' => [
                $students[0]->id => 'present',
                $students[1]->id => 'absent',
                $students[2]->id => 'late',
            ],
        ];

        $this->actingAs($user)
            ->post('/attendances/store-bulk', $attendanceData)
            ->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[0]->id,
            'status' => 'present',
        ]);
        
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[1]->id,
            'status' => 'absent',
        ]);
    }

    public function test_teacher_can_enter_marks(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        $subject = $subjects->first();
        
        $exam = Exam::factory()->create(['class_id' => $class->id]);
        $students = Student::factory()->count(2)->create(['class_id' => $class->id]);

        $marksData = [
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'marks' => [
                $students[0]->id => ['marks_obtained' => 85, 'total_marks' => 100],
                $students[1]->id => ['marks_obtained' => 92, 'total_marks' => 100],
            ],
        ];

        $this->actingAs($user)
            ->post('/marks/store', $marksData)
            ->assertRedirect();

        $this->assertDatabaseHas('results', [
            'student_id' => $students[0]->id,
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'marks_obtained' => 85,
        ]);

        $this->assertDatabaseHas('results', [
            'student_id' => $students[1]->id,
            'marks_obtained' => 92,
        ]);
    }

    public function test_teacher_can_view_class_results(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        $subject = $subjects->first();
        
        $exam = Exam::factory()->create(['class_id' => $class->id]);
        $student = Student::factory()->create(['class_id' => $class->id]);
        
        Result::factory()->create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'marks_obtained' => 88,
        ]);

        $response = $this->actingAs($user)
            ->get("/results?exam_id={$exam->id}&class_id={$class->id}");

        $response->assertStatus(200)
            ->assertSee($student->user->name)
            ->assertSee('88');
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();

        $this->actingAs($user)
            ->get('/users')
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/departments/create')
            ->assertStatus(403);
    }

    public function test_teacher_cannot_edit_other_teacher_lesson_plans(): void
    {
        [$user, $teacher, $subjects, $class] = $this->makeTeacherWithSubjects();
        
        $otherTeacher = Teacher::factory()->create();
        $otherLessonPlan = LessonPlan::factory()->create([
            'teacher_id' => $otherTeacher->id,
        ]);

        $this->actingAs($user)
            ->get("/lesson-plans/{$otherLessonPlan->id}/edit")
            ->assertStatus(403);

        $this->actingAs($user)
            ->put("/lesson-plans/{$otherLessonPlan->id}", ['topic' => 'Updated'])
            ->assertStatus(403);
    }

    public function test_teacher_with_missing_record_sees_error(): void
    {
        // Create user with role='teacher' but NO teacher record
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/profile')
            ->assertSessionHas('error');
        
        $this->assertStringContainsString('teacher profile is incomplete', session('error'));
    }
}

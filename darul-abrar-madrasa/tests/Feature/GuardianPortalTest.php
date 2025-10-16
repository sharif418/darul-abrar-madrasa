<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\StudyMaterial;
use App\Models\Fee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuardianPortalTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuardianWithStudent(bool $financial = true): array
    {
        /** @var Guardian $guardian */
        $guardian = \Database\Factories\GuardianFactory::new()->create();
        /** @var Student $student */
        $student = \Database\Factories\StudentFactory::new()->create();

        // Link guardian to student with pivot permissions
        $guardian->students()->attach($student->id, [
            'relationship' => 'father',
            'is_primary_guardian' => true,
            'can_pickup' => true,
            'financial_responsibility' => $financial,
            'receive_notifications' => true,
            'notes' => null,
        ]);

        return [$guardian->user, $guardian, $student];
    }

    public function test_guardian_can_access_dashboard(): void
    {
        [$user, $guardian, $student] = $this->makeGuardianWithStudent();

        $this->actingAs($user)
            ->get('/guardian/dashboard')
            ->assertStatus(200);
    }

    public function test_guardian_can_view_children_list(): void
    {
        [$user, $guardian, $student] = $this->makeGuardianWithStudent();

        $this->actingAs($user)
            ->get('/guardian/children')
            ->assertStatus(200)
            ->assertSee($student->user->name);
    }

    public function test_guardian_can_view_child_attendance(): void
    {
        [$user, $guardian, $student] = $this->makeGuardianWithStudent();

        $this->actingAs($user)
            ->get("/guardian/children/{$student->id}/attendance")
            ->assertStatus(200);
    }

    public function test_guardian_can_view_child_fees_when_financially_responsible(): void
    {
        [$user, $guardian, $student] = $this->makeGuardianWithStudent(true);
        // Create a fee for student
        \Database\Factories\FeeFactory::new()->for($student)->create();

        $this->actingAs($user)
            ->get("/guardian/children/{$student->id}/fees")
            ->assertStatus(200);
    }

    public function test_guardian_without_financial_responsibility_cannot_view_fees(): void
    {
        [$user, $guardian, $student] = $this->makeGuardianWithStudent(false);
        \Database\Factories\FeeFactory::new()->for($student)->create();

        $this->actingAs($user)
            ->get("/guardian/children/{$student->id}/fees")
            ->assertStatus(403);
    }

    public function test_guardian_cannot_view_other_child_data(): void
    {
        // Guardian A with Student A
        [$userA, $guardianA, $studentA] = $this->makeGuardianWithStudent();
        // Guardian B with Student B
        [$userB, $guardianB, $studentB] = $this->makeGuardianWithStudent();

        // Guardian A tries to access Guardian B's child
        $this->actingAs($userA)
            ->get("/guardian/children/{$studentB->id}")
            ->assertStatus(403);

        $this->actingAs($userA)
            ->get("/guardian/children/{$studentB->id}/attendance")
            ->assertStatus(403);

        $this->actingAs($userA)
            ->get("/guardian/children/{$studentB->id}/results")
            ->assertStatus(403);
    }

    public function test_guardian_can_download_study_materials(): void
    {
        Storage::fake('public');

        // Build class, subject and link guardian->student in that class
        $class = \Database\Factories\ClassRoomFactory::new()->create();
        $teacher = \Database\Factories\TeacherFactory::new()->create();
        $subject = \Database\Factories\SubjectFactory::new()->assignedTo($teacher)->state(['class_id' => $class->id])->create();

        [$user, $guardian, $student] = $this->makeGuardianWithStudent();
        // Move student to same class as material
        $student->update(['class_id' => $class->id]);

        // Put a fake file for material
        $path = 'study_materials/test-doc.pdf';
        Storage::disk('public')->put($path, 'dummy');

        $material = StudyMaterial::create([
            'title' => 'Syllabus',
            'description' => 'PDF',
            'content_type' => 'document',
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'is_published' => true,
            'file_path' => $path,
        ]);

        $this->actingAs($user)
            ->get("/study-materials/{$material->id}/download")
            ->assertStatus(200)
            ->assertHeader('content-disposition');
    }
}

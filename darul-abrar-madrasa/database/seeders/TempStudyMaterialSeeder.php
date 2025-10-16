<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\StudyMaterial;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

class TempStudyMaterialSeeder extends Seeder
{
    /**
     * Seed a published study material accessible to the guardian's linked student's class,
     * and ensure a downloadable file exists on the public disk.
     */
    public function run(): void
    {
        // Pick the first available student (guardian seeder links guardian to the first student)
        $student = Student::query()->first();
        if (!$student) {
            $this->command?->warn('TempStudyMaterialSeeder: No students found. Skipping study material seed.');
            return;
        }

        // Try to find a subject for the student's class; fall back to any subject
        $subject = Subject::query()->where('class_id', $student->class_id)->first();
        if (!$subject) {
            $subject = Subject::query()->first();
        }
        if (!$subject) {
            $this->command?->warn('TempStudyMaterialSeeder: No subjects found. Skipping study material seed.');
            return;
        }

        // Resolve a teacher (prefer subject's assigned teacher, otherwise any teacher)
        $teacher = $subject->teacher ?? Teacher::query()->first();
        if (!$teacher) {
            $this->command?->warn('TempStudyMaterialSeeder: No teachers found. Skipping study material seed.');
            return;
        }

        // Ensure a test file exists on the public disk
        $dir = 'study_materials';
        $filename = 'smoke_material.pdf';
        $path = $dir . '/' . $filename;

        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        if (!Storage::disk('public')->exists($path)) {
            $pdfStub = "%PDF-1.4\n% Smoke Test PDF placeholder\n";
            Storage::disk('public')->put($path, $pdfStub);
        }

        // Create or update a published StudyMaterial for the student's class
        $material = StudyMaterial::updateOrCreate(
            [
                'title'     => 'Smoke Test Material',
                'class_id'  => $student->class_id,
            ],
            [
                'teacher_id'   => $teacher->id,
                'subject_id'   => $subject->id,
                'description'  => 'This is a smoke-test study material for automated download checks.',
                'file_path'    => $path,
                'content_type' => 'document', // aligns with controller validation types
                'is_published' => true,
            ]
        );

        $this->command?->info('TempStudyMaterialSeeder: Study material ensured with ID '.$material->id.' at '.$path);
    }
}

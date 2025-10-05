<?php

namespace App\Repositories;

use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class ExamRepository
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    /**
     * Get all exams with filters and pagination
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->exam->with(['class.department']);

        // Search filter
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        // Status filter (upcoming, ongoing, completed)
        if (!empty($filters['status'])) {
            $now = now();
            switch ($filters['status']) {
                case 'upcoming':
                    $query->where('start_date', '>', $now);
                    break;
                case 'ongoing':
                    $query->where('start_date', '<=', $now)
                          ->where('end_date', '>=', $now);
                    break;
                case 'completed':
                    $query->where('end_date', '<', $now);
                    break;
            }
        }

        // Result status filter
        if (isset($filters['result_status'])) {
            $query->where('is_result_published', $filters['result_status']);
        }

        return $query->latest('start_date')->paginate($perPage);
    }

    /**
     * Create a new exam
     */
    public function create($data)
    {
        return $this->exam->create([
            'name' => $data['name'],
            'class_id' => $data['class_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_result_published' => $data['is_result_published'] ?? false,
        ]);
    }

    /**
     * Update an exam
     */
    public function update($exam, $data)
    {
        // Prevent unpublishing results if already published
        if ($exam->is_result_published && !($data['is_result_published'] ?? true)) {
            throw new \Exception('Cannot unpublish results once they have been published.');
        }

        $exam->update([
            'name' => $data['name'],
            'class_id' => $data['class_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_result_published' => $data['is_result_published'] ?? false,
        ]);

        return $exam->fresh(['class.department']);
    }

    /**
     * Delete an exam
     */
    public function delete($exam)
    {
        // Check if exam has results
        $resultsCount = $exam->results()->count();
        
        if ($resultsCount > 0) {
            throw new \Exception('Cannot delete exam with existing results. Please delete results first.');
        }

        return $exam->delete();
    }

    /**
     * Get exam with statistics
     */
    public function getWithStatistics($examId)
    {
        $exam = $this->exam->with([
            'class.department',
            'class.subjects',
            'results.student.user',
            'results.subject'
        ])->findOrFail($examId);

        // Calculate statistics if results are published
        if ($exam->is_result_published) {
            $totalStudents = $exam->class->students()->count();
            
            // Get unique students who have results
            $studentsWithResults = $exam->results->pluck('student_id')->unique()->count();
            
            // Calculate pass/fail based on overall results
            $passedStudents = 0;
            $failedStudents = 0;
            
            // Group results by student
            $studentResults = $exam->results->groupBy('student_id');
            
            foreach ($studentResults as $studentId => $results) {
                $allPassed = true;
                foreach ($results as $result) {
                    if ($result->grade === 'F') {
                        $allPassed = false;
                        break;
                    }
                }
                
                if ($allPassed) {
                    $passedStudents++;
                } else {
                    $failedStudents++;
                }
            }
            
            $passRate = $studentsWithResults > 0 ? ($passedStudents / $studentsWithResults) * 100 : 0;
            
            $exam->statistics = [
                'totalStudents' => $totalStudents,
                'studentsWithResults' => $studentsWithResults,
                'passedStudents' => $passedStudents,
                'failedStudents' => $failedStudents,
                'passRate' => round($passRate, 2),
            ];
        }

        return $exam;
    }

    /**
     * Publish exam results
     */
    public function publishResults($exam)
    {
        // Validate exam is completed
        if ($exam->end_date > now()) {
            return [
                'success' => false,
                'message' => 'Cannot publish results for an exam that has not ended yet.',
            ];
        }

        // Get all subjects for the class
        $subjects = $exam->class->subjects;
        $students = $exam->class->students;
        
        // Check if all students have results for all subjects
        $missingResults = [];
        
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                $resultExists = $exam->results()
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->exists();
                
                if (!$resultExists) {
                    $missingResults[] = [
                        'student' => $student->user->name,
                        'subject' => $subject->name,
                    ];
                }
            }
        }

        if (!empty($missingResults)) {
            return [
                'success' => false,
                'message' => 'Cannot publish results. Some students are missing results for some subjects.',
                'missing' => $missingResults,
            ];
        }

        // Publish results
        $exam->update(['is_result_published' => true]);

        return [
            'success' => true,
            'message' => 'Results published successfully.',
        ];
    }
}

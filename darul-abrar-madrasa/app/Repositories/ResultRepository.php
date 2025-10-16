<?php

namespace App\Repositories;

use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ResultRepository
{
    protected $result;

    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    /**
     * Get all results with filters and pagination
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->result->with(['student.user', 'exam', 'subject']);

        // Exam filter
        if (!empty($filters['exam_id'])) {
            $query->where('exam_id', $filters['exam_id']);
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        // Subject filter
        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        // Student filter
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get statistics for exam and subject
     */
    public function getStatistics($examId, $subjectId)
    {
        $results = $this->result
            ->where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->get();

        $totalStudents = $results->count();
        $passedStudents = $results->where('grade', '!=', 'F')->count();
        $failedStudents = $results->where('grade', 'F')->count();
        $passRate = $totalStudents > 0 ? ($passedStudents / $totalStudents) * 100 : 0;
        $averageMark = $results->avg('marks_obtained');

        return [
            'totalStudents' => $totalStudents,
            'passedStudents' => $passedStudents,
            'failedStudents' => $failedStudents,
            'passRate' => round($passRate, 2),
            'averageMark' => round($averageMark, 2),
        ];
    }

    /**
     * Store bulk results
     */
    public function storeBulk($examId, $subjectId, $studentMarks, $createdBy)
    {
        return DB::transaction(function () use ($examId, $subjectId, $studentMarks, $createdBy) {
            $count = 0;

            foreach ($studentMarks as $studentId => $data) {
                $payload = [
                    'marks_obtained' => $data['marks_obtained'],
                    'remarks' => $data['remarks'] ?? null,
                    'created_by' => $createdBy,
                ];
                
                $result = $this->result->updateOrCreate(
                    [
                        'exam_id' => $examId,
                        'subject_id' => $subjectId,
                        'student_id' => $studentId,
                    ],
                    $payload
                );

                // Calculate grade and GPA using model method and save
                $result->calculateGradeAndGpa()->save();
                
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update a result
     */
    public function update($result, $data)
    {
        // Check if results are published
        if ($result->exam->is_result_published) {
            throw new \Exception('Cannot modify results for an exam with published results.');
        }

        $result->update([
            'marks_obtained' => $data['marks_obtained'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        // Recalculate grade and GPA and save
        $result->calculateGradeAndGpa()->save();

        return $result->fresh(['student.user', 'exam', 'subject']);
    }

    /**
     * Delete a result
     */
    public function delete($result)
    {
        // Check if results are published
        if ($result->exam->is_result_published) {
            throw new \Exception('Cannot delete results for an exam with published results.');
        }

        return $result->delete();
    }

    /**
     * Get student results
     */
    public function getStudentResults($studentId, $examId = null)
    {
        $query = $this->result
            ->with(['exam.class', 'subject'])
            ->where('student_id', $studentId);

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        $results = $query->get();

        // Group by exam
        $examResults = $results->groupBy('exam_id')->map(function ($examResults) {
            $exam = $examResults->first()->exam;
            
            $totalMarks = $examResults->sum('marks_obtained');
            $totalFullMarks = $examResults->sum(function ($result) {
                return $result->subject->full_mark;
            });
            
            $percentage = $totalFullMarks > 0 ? ($totalMarks / $totalFullMarks) * 100 : 0;
            $averageGpa = $examResults->avg('gpa_point');
            
            $allPassed = $examResults->every(function ($result) {
                return $result->grade !== 'F';
            });

            return [
                'exam' => $exam,
                'results' => $examResults,
                'summary' => [
                    'totalMarks' => $totalMarks,
                    'totalFullMarks' => $totalFullMarks,
                    'percentage' => round($percentage, 2),
                    'averageGpa' => round($averageGpa, 2),
                    'status' => $allPassed ? 'Passed' : 'Failed',
                ],
            ];
        });

        return $examResults;
    }

    /**
     * Get existing results for bulk entry form
     */
    public function getExistingResults($examId, $subjectId)
    {
        return $this->result
            ->where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('student_id');
    }
}

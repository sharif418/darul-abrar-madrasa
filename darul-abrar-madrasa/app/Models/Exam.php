<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'class_id',
        'start_date',
        'end_date',
        'description',
        'is_active',
        'is_result_published',
        'pass_gpa',
        'fail_limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_result_published' => 'boolean',
        'pass_gpa' => 'decimal:2',
        'fail_limit' => 'integer',
    ];

    /**
     * Get the class that owns the exam.
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the results for the exam.
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Check if the exam is ongoing.
     */
    public function getIsOngoingAttribute()
    {
        $now = now();
        return $this->start_date->lte($now) && $this->end_date->gte($now);
    }

    /**
     * Check if the exam is upcoming.
     */
    public function getIsUpcomingAttribute()
    {
        return $this->start_date->gt(now());
    }

    /**
     * Check if the exam is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->end_date->lt(now());
    }

    /**
     * Get the duration of the exam in days.
     */
    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope a query to only include active exams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include exams with published results.
     */
    public function scopePublishedResults($query)
    {
        return $query->where('is_result_published', true);
    }

    /**
     * Scope a query to only include ongoing exams.
     */
    public function scopeOngoing($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope a query to only include upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include completed exams.
     */
    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }
    
    /**
     * Calculate results for all students in this exam.
     * 
     * @return array
     */
    public function calculateResults()
    {
        $classId = $this->class_id;
        $examId = $this->id;
        
        // Get all students in this class
        $students = Student::where('class_id', $classId)->get();
        $results = [];
        
        foreach ($students as $student) {
            $studentResult = Result::getOverallResult($student->id, $examId);
            
            // Determine if the student passed based on exam rules
            $isPassed = $studentResult['is_passed'];
            
            // If the student passed all subjects, check if they meet the minimum GPA requirement
            if ($isPassed && $this->pass_gpa > 0) {
                $isPassed = $studentResult['average_gpa'] >= $this->pass_gpa;
            }
            
            // If the student failed some subjects, check if they exceed the fail limit
            if (!$studentResult['is_passed'] && $this->fail_limit > 0) {
                $isPassed = $studentResult['failed_subjects'] <= $this->fail_limit;
            }
            
            $studentResult['final_result'] = $isPassed ? 'PASSED' : 'FAILED';
            $results[$student->id] = $studentResult;
        }
        
        return $results;
    }
    
    /**
     * Get the result for a specific student in this exam.
     * 
     * @param int $studentId
     * @return array
     */
    public function getStudentResult($studentId)
    {
        $studentResult = Result::getOverallResult($studentId, $this->id);
        
        // Determine if the student passed based on exam rules
        $isPassed = $studentResult['is_passed'];
        
        // If the student passed all subjects, check if they meet the minimum GPA requirement
        if ($isPassed && $this->pass_gpa > 0) {
            $isPassed = $studentResult['average_gpa'] >= $this->pass_gpa;
        }
        
        // If the student failed some subjects, check if they exceed the fail limit
        if (!$studentResult['is_passed'] && $this->fail_limit > 0) {
            $isPassed = $studentResult['failed_subjects'] <= $this->fail_limit;
        }
        
        $studentResult['final_result'] = $isPassed ? 'PASSED' : 'FAILED';
        
        return $studentResult;
    }
    
    /**
     * Get the class rank list for this exam.
     * 
     * @return array
     */
    public function getClassRankList()
    {
        $results = $this->calculateResults();
        
        // Sort by average GPA in descending order
        uasort($results, function($a, $b) {
            return $b['average_gpa'] <=> $a['average_gpa'];
        });
        
        // Add rank to each student
        $rank = 1;
        $previousGpa = null;
        $skipRank = 0;
        
        foreach ($results as $studentId => &$result) {
            if ($previousGpa !== null && $previousGpa == $result['average_gpa']) {
                // Same GPA as previous student, assign same rank
                $skipRank++;
            } else {
                // Different GPA, assign new rank
                $rank += $skipRank;
                $skipRank = 0;
            }
            
            $result['rank'] = $rank;
            $previousGpa = $result['average_gpa'];
            
            // Add student information
            $student = Student::find($studentId);
            $result['student'] = $student;
        }
        
        return $results;
    }
}
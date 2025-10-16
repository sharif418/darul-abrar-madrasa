<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Exam
 * 
 * @property int $id
 * @property string $name
 * @property int $class_id
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property string|null $description
 * @property bool $is_active
 * @property bool $is_result_published
 * @property float|null $pass_gpa
 * @property int|null $fail_limit
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read ClassRoom $class
 * @property-read \Illuminate\Database\Eloquent\Collection|Result[] $results
 * @property-read bool $is_ongoing
 * @property-read bool $is_upcoming
 * @property-read bool $is_completed
 * @property-read int $duration
 */
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the results for the exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Scope a query to filter by class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $classId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope a query to search by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Check if the exam is ongoing.
     *
     * @return bool
     */
    public function getIsOngoingAttribute()
    {
        $now = now();
        return $this->start_date->lte($now) && $this->end_date->gte($now);
    }

    /**
     * Check if the exam is upcoming.
     *
     * @return bool
     */
    public function getIsUpcomingAttribute()
    {
        return $this->start_date->gt(now());
    }

    /**
     * Check if the exam is completed.
     *
     * @return bool
     */
    public function getIsCompletedAttribute()
    {
        return $this->end_date->lt(now());
    }

    /**
     * Get the duration of the exam in days.
     *
     * @return int
     */
    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope a query to only include active exams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include exams with published results.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublishedResults($query)
    {
        return $query->where('is_result_published', true);
    }

    /**
     * Scope a query to only include ongoing exams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOngoing($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope a query to only include upcoming exams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include completed exams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Check if results can be published.
     *
     * @return bool
     */
    public function canPublishResults()
    {
        // Exam must be completed
        if (!$this->is_completed) {
            return false;
        }

        // Check if all students have results for all subjects
        $classId = $this->class_id;
        $examId = $this->id;
        
        $studentsCount = Student::where('class_id', $classId)->where('is_active', true)->count();
        $subjectsCount = Subject::where('class_id', $classId)->where('is_active', true)->count();
        
        $expectedResultsCount = $studentsCount * $subjectsCount;
        $actualResultsCount = Result::where('exam_id', $examId)->count();
        
        return $actualResultsCount >= $expectedResultsCount;
    }

    /**
     * Check if results can be edited.
     *
     * @return bool
     */
    public function canEditResults()
    {
        return !$this->is_result_published;
    }

    /**
     * Get results completion percentage.
     *
     * @return float
     */
    public function getResultsCompletionPercentage()
    {
        $classId = $this->class_id;
        $examId = $this->id;
        
        $studentsCount = Student::where('class_id', $classId)->where('is_active', true)->count();
        $subjectsCount = Subject::where('class_id', $classId)->where('is_active', true)->count();
        
        if ($studentsCount == 0 || $subjectsCount == 0) {
            return 0;
        }
        
        $expectedResultsCount = $studentsCount * $subjectsCount;
        $actualResultsCount = Result::where('exam_id', $examId)->count();
        
        return ($actualResultsCount / $expectedResultsCount) * 100;
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
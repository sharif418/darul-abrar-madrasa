<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Result
 * 
 * @property int $id
 * @property int $student_id
 * @property int $exam_id
 * @property int $subject_id
 * @property float $marks_obtained
 * @property string $grade
 * @property float $gpa_point
 * @property bool $is_passed
 * @property string|null $remarks
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read Student $student
 * @property-read Exam $exam
 * @property-read Subject $subject
 * @property-read User|null $createdBy
 * @property-read float $percentage
 */
class Result extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'exam_id',
        'subject_id',
        'marks_obtained',
        'grade',
        'gpa_point',
        'is_passed',
        'remarks',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'gpa_point' => 'decimal:2',
        'is_passed' => 'boolean',
    ];

    /**
     * Get the student that owns the result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the exam that owns the result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the subject that owns the result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user who created the result.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to filter by class through student relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $classId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClass($query, $classId)
    {
        return $query->whereHas('student', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        });
    }

    /**
     * Scope a query to only include results of published exams.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->whereHas('exam', function ($q) {
            $q->where('is_result_published', true);
        });
    }

    /**
     * Scope a query to only include results for a specific exam.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $examId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Scope a query to only include results for a specific student.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include results for a specific subject.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $subjectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope a query to only include passed results.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }

    /**
     * Scope a query to only include failed results.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('is_passed', false);
    }

    /**
     * Get the percentage of marks obtained.
     *
     * @return float
     */
    public function getPercentageAttribute()
    {
        if (!$this->relationLoaded('subject')) {
            $this->load('subject');
        }
        return ($this->marks_obtained / $this->subject->full_mark) * 100;
    }

    /**
     * Check if result can be edited.
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->relationLoaded('exam')) {
            $this->load('exam');
        }
        return !$this->exam->is_result_published;
    }

    /**
     * Check if result can be deleted.
     *
     * @return bool
     */
    public function canDelete()
    {
        if (!$this->relationLoaded('exam')) {
            $this->load('exam');
        }
        return !$this->exam->is_result_published;
    }

    /**
     * Calculate and set the grade and GPA based on marks obtained.
     *
     * @return $this
     */
    public function calculateGradeAndGpa()
    {
        // Load subject if not already loaded
        if (!$this->relationLoaded('subject')) {
            $this->load('subject');
        }
        
        // Calculate percentage
        $percentage = ($this->marks_obtained / $this->subject->full_mark) * 100;
        
        // Check if passed (marks >= pass_mark)
        $this->is_passed = $this->marks_obtained >= $this->subject->pass_mark;
        
        // Get grading scale based on percentage
        $gradingScale = GradingScale::getGradeForMark($percentage);
        
        if ($gradingScale && $this->is_passed) {
            $this->grade = $gradingScale->grade_name;
            $this->gpa_point = $gradingScale->gpa_point;
        } else {
            // Failed or no matching grade
            $this->grade = 'F';
            $this->gpa_point = 0.00;
            $this->is_passed = false;
        }
        
        return $this;
    }
    
    /**
     * Get the overall result for a student in an exam.
     *
     * @param int $studentId
     * @param int $examId
     * @return array
     */
    public static function getOverallResult($studentId, $examId)
    {
        $results = self::with('subject')
            ->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->get();
            
        $totalMarks = $results->sum('marks_obtained');
        $totalSubjects = $results->count();
        $totalGpaPoints = $results->sum('gpa_point');
        $averageGpa = $totalSubjects > 0 ? $totalGpaPoints / $totalSubjects : 0;
        $failedSubjects = $results->where('is_passed', false)->count();
        $isPassed = $failedSubjects === 0;
        
        // Get the overall grade based on average GPA
        $overallGrade = 'F';
        if ($isPassed) {
            $gradingScales = GradingScale::active()->orderBy('gpa_point', 'desc')->get();
            foreach ($gradingScales as $scale) {
                if ($averageGpa >= $scale->gpa_point) {
                    $overallGrade = $scale->grade_name;
                    break;
                }
            }
        }
        
        return [
            'total_marks' => $totalMarks,
            'total_subjects' => $totalSubjects,
            'average_gpa' => round($averageGpa, 2),
            'overall_grade' => $overallGrade,
            'is_passed' => $isPassed,
            'failed_subjects' => $failedSubjects,
            'results' => $results
        ];
    }
}

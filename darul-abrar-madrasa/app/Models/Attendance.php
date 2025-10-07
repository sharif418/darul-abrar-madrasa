<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Attendance
 * 
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property \Carbon\Carbon $date
 * @property string $status
 * @property string|null $remarks
 * @property int $marked_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read Student $student
 * @property-read ClassRoom $class
 * @property-read User $markedBy
 */
class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'remarks',
        'marked_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the student that owns the attendance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class that owns the attendance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the user who marked the attendance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope a query to only include attendances for a specific class.
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
     * Scope a query to only include attendances for a specific student.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include present attendances.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent attendances.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late attendances.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope a query to filter by specific month and year.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $month
     * @param int $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMonth($query, $month, $year)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Scope a query to only include attendances for a specific date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include attendances with a specific status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the attendance is present.
     *
     * @return bool
     */
    public function isPresent()
    {
        return $this->status === 'present';
    }

    /**
     * Check if the attendance is absent.
     *
     * @return bool
     */
    public function isAbsent()
    {
        return $this->status === 'absent';
    }

    /**
     * Check if the attendance is late.
     *
     * @return bool
     */
    public function isLate()
    {
        return $this->status === 'late';
    }

    /**
     * Get the status badge color.
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'leave' => 'info',
            'half_day' => 'secondary',
            default => 'secondary',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TimetableEntry
 * 
 * @property int $id
 * @property int $timetable_id
 * @property int $class_id
 * @property int $subject_id
 * @property int|null $teacher_id
 * @property int $period_id
 * @property string $day_of_week
 * @property string|null $room_number
 * @property string|null $notes
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read Timetable $timetable
 * @property-read ClassRoom $class
 * @property-read Subject $subject
 * @property-read Teacher|null $teacher
 * @property-read Period $period
 */
class TimetableEntry extends Model
{
    use HasFactory;

    const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'timetable_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'period_id',
        'day_of_week',
        'room_number',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the timetable that owns the entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    /**
     * Get the class for this entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the subject for this entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher for this entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the period for this entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Scope a query to only include active entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by timetable.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $timetableId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTimetable($query, $timetableId)
    {
        return $query->where('timetable_id', $timetableId);
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
     * Scope a query to filter by teacher.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to filter by subject.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $subjectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope a query to filter by day of week.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $day
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope a query to filter by period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $periodId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('period_id', $periodId);
    }

    /**
     * Scope a query to eager load all relationships.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['timetable', 'class', 'subject', 'teacher', 'period']);
    }

    /**
     * Check if the entry has a teacher assigned.
     *
     * @return bool
     */
    public function hasTeacher()
    {
        return !is_null($this->teacher_id);
    }

    /**
     * Check if the entry has a room assigned.
     *
     * @return bool
     */
    public function hasRoom()
    {
        return !is_null($this->room_number);
    }

    /**
     * Get a full description of the entry.
     *
     * @return string
     */
    public function getFullDescription()
    {
        $parts = [];
        
        if ($this->class) {
            $parts[] = $this->class->name;
        }
        
        if ($this->subject) {
            $parts[] = $this->subject->name;
        }
        
        if ($this->teacher) {
            $parts[] = $this->teacher->user->name ?? 'Teacher';
        }
        
        if ($this->room_number) {
            $parts[] = "Room {$this->room_number}";
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Check if this entry conflicts with another entry for the same teacher.
     *
     * @param TimetableEntry $otherEntry
     * @return bool
     */
    public function conflictsWithTeacher(TimetableEntry $otherEntry)
    {
        if (!$this->teacher_id || !$otherEntry->teacher_id) {
            return false;
        }
        
        return $this->teacher_id === $otherEntry->teacher_id &&
               $this->day_of_week === $otherEntry->day_of_week &&
               $this->period_id === $otherEntry->period_id &&
               $this->id !== $otherEntry->id;
    }

    /**
     * Check if this entry conflicts with another entry for the same room.
     *
     * @param TimetableEntry $otherEntry
     * @return bool
     */
    public function conflictsWithRoom(TimetableEntry $otherEntry)
    {
        if (!$this->room_number || !$otherEntry->room_number) {
            return false;
        }
        
        return $this->room_number === $otherEntry->room_number &&
               $this->day_of_week === $otherEntry->day_of_week &&
               $this->period_id === $otherEntry->period_id &&
               $this->id !== $otherEntry->id;
    }
}

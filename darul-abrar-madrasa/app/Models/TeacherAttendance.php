<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Class TeacherAttendance
 *
 * @property int $id
 * @property int $teacher_id
 * @property \Carbon\Carbon $date
 * @property string $status
 * @property string|null $check_in_time
 * @property string|null $check_out_time
 * @property string|null $remarks
 * @property int $marked_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Teacher $teacher
 * @property-read User $markedBy
 * @property-read string $status_color
 */
class TeacherAttendance extends Model
{
    use HasFactory;

    /**
     * Status constants
     */
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LEAVE = 'leave';
    const STATUS_HALF_DAY = 'half_day';
    
    const STATUSES = [
        self::STATUS_PRESENT,
        self::STATUS_ABSENT,
        self::STATUS_LEAVE,
        self::STATUS_HALF_DAY,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'teacher_id',
        'date',
        'status',
        'check_in_time',
        'check_out_time',
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
        'check_in_time' => 'string',
        'check_out_time' => 'string',
    ];

    /**
     * Get the teacher that owns the attendance.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the user who marked the attendance.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
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
     * Scope a query to only include present attendance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    /**
     * Scope a query to only include absent attendance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }

    /**
     * Scope a query to only include leave attendance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', self::STATUS_LEAVE);
    }

    /**
     * Scope a query to only include half day attendance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHalfDay($query)
    {
        return $query->where('status', self::STATUS_HALF_DAY);
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
     * Scope a query to filter by month.
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
     * Scope a query to filter by specific date.
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
     * Scope a query to filter by status.
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
     * Scope a query to eager load relationships.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['teacher', 'markedBy']);
    }

    /**
     * Check if the attendance is marked as present.
     *
     * @return bool
     */
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_PRESENT;
    }

    /**
     * Check if the attendance is marked as absent.
     *
     * @return bool
     */
    public function isAbsent(): bool
    {
        return $this->status === self::STATUS_ABSENT;
    }

    /**
     * Check if the attendance is marked as on leave.
     *
     * @return bool
     */
    public function isOnLeave(): bool
    {
        return $this->status === self::STATUS_LEAVE;
    }

    /**
     * Check if the attendance is marked as half day.
     *
     * @return bool
     */
    public function isHalfDay(): bool
    {
        return $this->status === self::STATUS_HALF_DAY;
    }

    /**
     * Check if the teacher has checked in.
     *
     * @return bool
     */
    public function hasCheckedIn(): bool
    {
        return $this->check_in_time !== null;
    }

    /**
     * Check if the teacher has checked out.
     *
     * @return bool
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out_time !== null;
    }

    /**
     * Calculate working hours between check-in and check-out.
     *
     * @return float|null
     */
    public function getWorkingHours(): ?float
    {
        if (!$this->hasCheckedIn() || !$this->hasCheckedOut()) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);

        return round($checkIn->diffInMinutes($checkOut, true) / 60, 2);
    }

    /**
     * Check if the teacher was late (after 9:00 AM).
     *
     * @param string $threshold Default is '09:00'
     * @return bool
     */
    public function isLate(string $threshold = '09:00'): bool
    {
        if (!$this->hasCheckedIn()) {
            return false;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $thresholdTime = Carbon::parse($threshold);

        return $checkIn->isAfter($thresholdTime);
    }

    /**
     * Check if the teacher left early (before 4:00 PM).
     *
     * @param string $threshold Default is '16:00'
     * @return bool
     */
    public function isEarlyLeave(string $threshold = '16:00'): bool
    {
        if (!$this->hasCheckedOut()) {
            return false;
        }

        $checkOut = Carbon::parse($this->check_out_time);
        $thresholdTime = Carbon::parse($threshold);

        return $checkOut->isBefore($thresholdTime);
    }

    /**
     * Get the status color for badge display.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PRESENT => 'success',
            self::STATUS_ABSENT => 'danger',
            self::STATUS_LEAVE => 'info',
            self::STATUS_HALF_DAY => 'warning',
            default => 'secondary',
        };
    }
}

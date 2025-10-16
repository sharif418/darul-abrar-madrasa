<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Period
 * 
 * @property int $id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property string $day_of_week
 * @property int $order
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|TimetableEntry[] $timetableEntries
 */
class Period extends Model
{
    use HasFactory;

    const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'day_of_week',
        'order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the timetable entries that use this period.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /**
     * Scope a query to only include active periods.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter periods by day of week.
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
     * Scope a query to order periods by order column.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope a query to search periods by name.
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
     * Get the duration of the period in minutes.
     *
     * @return int
     */
    public function getDurationInMinutes()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        return $start->diffInMinutes($end);
    }

    /**
     * Get formatted time range string.
     *
     * @return string
     */
    public function getFormattedTimeRange()
    {
        $start = Carbon::parse($this->start_time)->format('h:i A');
        $end = Carbon::parse($this->end_time)->format('h:i A');
        
        return "{$start} - {$end}";
    }

    /**
     * Check if this is a break time period.
     *
     * @return bool
     */
    public function isBreakTime()
    {
        $name = strtolower($this->name);
        return str_contains($name, 'break') || 
               str_contains($name, 'lunch') || 
               str_contains($name, 'recess');
    }

    /**
     * Check if this period conflicts with another period.
     *
     * @param Period $otherPeriod
     * @return bool
     */
    public function conflictsWith(Period $otherPeriod)
    {
        if ($this->day_of_week !== $otherPeriod->day_of_week) {
            return false;
        }

        $thisStart = Carbon::parse($this->start_time);
        $thisEnd = Carbon::parse($this->end_time);
        $otherStart = Carbon::parse($otherPeriod->start_time);
        $otherEnd = Carbon::parse($otherPeriod->end_time);

        return ($thisStart < $otherEnd) && ($thisEnd > $otherStart);
    }
}

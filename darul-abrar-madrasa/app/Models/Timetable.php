<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Timetable
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon $effective_from
 * @property \Carbon\Carbon|null $effective_to
 * @property bool $is_active
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|TimetableEntry[] $entries
 * @property-read bool $is_current
 * @property-read bool $is_expired
 */
class Timetable extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created the timetable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the timetable entries for this timetable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /**
     * Scope a query to only include active timetables.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include current timetables.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('effective_from', '<=', $today)
                     ->where(function ($q) use ($today) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', $today);
                     });
    }

    /**
     * Scope a query to only include upcoming timetables.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('effective_from', '>', now()->toDateString());
    }

    /**
     * Scope a query to only include expired timetables.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('effective_to')
                     ->where('effective_to', '<', now()->toDateString());
    }

    /**
     * Scope a query to search timetables by name or description.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Check if the timetable is currently active.
     *
     * @return bool
     */
    public function getIsCurrentAttribute()
    {
        $today = now();
        $isWithinDateRange = $this->effective_from <= $today && 
                            ($this->effective_to === null || $this->effective_to >= $today);
        
        return $isWithinDateRange;
    }

    /**
     * Check if the timetable has expired.
     *
     * @return bool
     */
    public function getIsExpiredAttribute()
    {
        return $this->effective_to !== null && $this->effective_to < now();
    }

    /**
     * Check if the timetable can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted()
    {
        return $this->entries()->count() === 0;
    }

    /**
     * Get the count of entries in this timetable.
     *
     * @return int
     */
    public function getEntriesCount()
    {
        return $this->entries()->count();
    }

    /**
     * Get the count of distinct classes in this timetable.
     *
     * @return int
     */
    public function getClassesCount()
    {
        return $this->entries()->distinct('class_id')->count('class_id');
    }

    /**
     * Get the duration of the timetable in days.
     *
     * @return int
     */
    public function getDurationInDays()
    {
        $endDate = $this->effective_to ?? now();
        return $this->effective_from->diffInDays($endDate);
    }
}

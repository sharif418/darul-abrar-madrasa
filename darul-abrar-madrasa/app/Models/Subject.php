<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subject
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $class_id
 * @property int|null $teacher_id
 * @property int $full_mark
 * @property int $pass_mark
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read ClassRoom $class
 * @property-read Teacher|null $teacher
 * @property-read \Illuminate\Database\Eloquent\Collection|Result[] $results
 */
class Subject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'class_id',
        'teacher_id',
        'full_mark',
        'pass_mark',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'full_mark' => 'integer',
        'pass_mark' => 'integer',
    ];

    /**
     * Get the class that owns the subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the teacher that teaches the subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the results for the subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Scope a query to only include active subjects.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Scope a query to search by name or code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * Check if subject has assigned teacher.
     *
     * @return bool
     */
    public function hasTeacher(): bool
    {
        return !is_null($this->teacher_id);
    }

    /**
     * Check if subject can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return $this->results()->count() === 0;
    }

    /**
     * Get pass percentage.
     *
     * @return float
     */
    public function getPassPercentage(): float
    {
        if ($this->full_mark == 0) {
            return 0;
        }
        return ($this->pass_mark / $this->full_mark) * 100;
    }
}

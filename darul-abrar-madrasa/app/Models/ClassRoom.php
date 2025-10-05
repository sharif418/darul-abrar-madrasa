<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClassRoom
 * 
 * @property int $id
 * @property string $name
 * @property int $department_id
 * @property string|null $class_numeric
 * @property string|null $section
 * @property int $capacity
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection|Student[] $students
 * @property-read \Illuminate\Database\Eloquent\Collection|Subject[] $subjects
 * @property-read \Illuminate\Database\Eloquent\Collection|Exam[] $exams
 * @property-read \Illuminate\Database\Eloquent\Collection|Attendance[] $attendances
 */
class ClassRoom extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'department_id',
        'class_numeric',
        'section',
        'capacity',
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
        'capacity' => 'integer',
    ];

    /**
     * Get the department that owns the class.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the students for the class.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the subjects for the class.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    /**
     * Get the exams for the class.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    /**
     * Get the attendances for the class.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Scope a query to only include active classes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by department.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to search by name, class_numeric, or section.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('class_numeric', 'like', "%{$search}%")
              ->orWhere('section', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to eager load department.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithDepartment($query)
    {
        return $query->with('department');
    }

    /**
     * Check if class has reached capacity.
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->getStudentsCount() >= $this->capacity;
    }

    /**
     * Get available seats in the class.
     *
     * @return int
     */
    public function getAvailableSeats(): int
    {
        return max(0, $this->capacity - $this->getStudentsCount());
    }

    /**
     * Get count of students in the class.
     *
     * @return int
     */
    public function getStudentsCount(): int
    {
        return $this->students()->where('is_active', true)->count();
    }
}

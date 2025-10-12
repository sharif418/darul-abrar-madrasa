<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Teacher
 * 
 * @property int $id
 * @property int $user_id
 * @property int $department_id
 * @property string $designation
 * @property string $qualification
 * @property string $phone
 * @property string $address
 * @property \Carbon\Carbon $joining_date
 * @property float $salary
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $user
 * @property-read Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection|Subject[] $subjects
 */
class Teacher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'designation',
        'qualification',
        'phone',
        'address',
        'joining_date',
        'salary',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the user that owns the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the teacher belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the subjects taught by the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Scope a query to only include active teachers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include teachers in a specific department.
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
     * Scope a query to search teachers by name, email, or designation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('designation', 'like', "%{$search}%");
    }

    /**
     * Scope a query to eager load user relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithUser($query)
    {
        return $query->with('user');
    }

    /**
     * Get the full name of the teacher.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Get the email of the teacher.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    /**
     * Get the status of the teacher.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the count of assigned subjects.
     *
     * @return int
     */
    public function getAssignedSubjectsCount()
    {
        return $this->subjects()->count();
    }

    /**
     * Get the count of assigned classes (unique classes from subjects).
     *
     * @return int
     */
    public function getAssignedClassesCount()
    {
        return $this->subjects()->distinct('class_id')->count('class_id');
    }

    /**
     * Get the years of experience.
     *
     * @return int
     */
    public function getYearsOfExperience()
    {
        return $this->joining_date->diffInYears(now());
    }

    /**
     * Check if teacher has any assigned subjects.
     *
     * @return bool
     */
    public function hasAssignedSubjects()
    {
        return $this->subjects()->exists();
    }
}

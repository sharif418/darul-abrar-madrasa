<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the students for the class.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the subjects for the class.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    /**
     * Get the exams for the class.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    /**
     * Get the attendances for the class.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}
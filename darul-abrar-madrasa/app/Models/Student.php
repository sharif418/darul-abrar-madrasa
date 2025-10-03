<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'class_id',
        'roll_number',
        'admission_number',
        'admission_date',
        'father_name',
        'mother_name',
        'guardian_phone',
        'guardian_email',
        'address',
        'date_of_birth',
        'gender',
        'blood_group',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'admission_date' => 'date',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class that the student belongs to.
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the attendances for the student.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the fees for the student.
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    /**
     * Get the results for the student.
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get the full name of the student.
     */
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Get the email of the student.
     */
    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    /**
     * Get the age of the student.
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'medical_conditions',
        'allergies',
        'medications',
        'special_needs',
        'doctor_name',
        'doctor_phone',
        'hospital_name',
        'health_insurance',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

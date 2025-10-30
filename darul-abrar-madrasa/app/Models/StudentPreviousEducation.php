<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPreviousEducation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_name',
        'class_grade',
        'passing_year',
        'result',
        'board',
        'reason_for_leaving',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

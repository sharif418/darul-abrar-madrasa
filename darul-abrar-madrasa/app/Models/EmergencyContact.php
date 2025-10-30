<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'phone',
        'relation',
        'address',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'national_id',
        'occupation',
        'designation',
        'office_address',
        'present_address',
        'permanent_address',
        'annual_income',
        'photo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'annual_income' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardians')
            ->withPivot(['relationship', 'is_primary', 'is_emergency_contact', 'can_pickup', 'receive_communication'])
            ->withTimestamps();
    }

    public function primaryWards()
    {
        return $this->belongsToMany(Student::class, 'student_guardians')
            ->wherePivot('is_primary', true)
            ->withPivot(['relationship', 'is_emergency_contact', 'can_pickup', 'receive_communication'])
            ->withTimestamps();
    }
}

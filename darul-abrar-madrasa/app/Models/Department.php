<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
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
    ];

    /**
     * Get the classes for the department.
     */
    public function classes()
    {
        return $this->hasMany(ClassRoom::class);
    }

    /**
     * Get the teachers for the department.
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grade_name',
        'gpa_point',
        'min_mark',
        'max_mark',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gpa_point' => 'decimal:2',
        'min_mark' => 'decimal:2',
        'max_mark' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active grading scales.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the grade for a given mark.
     *
     * @param float $mark
     * @return GradingScale|null
     */
    public static function getGradeForMark($mark)
    {
        return self::active()
            ->where('min_mark', '<=', $mark)
            ->where('max_mark', '>=', $mark)
            ->first();
    }
}
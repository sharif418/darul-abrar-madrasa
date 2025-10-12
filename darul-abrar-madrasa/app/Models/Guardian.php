<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'national_id',
        'occupation',
        'address',
        'phone',
        'alternative_phone',
        'email',
        'relationship_type',
        'is_primary_contact',
        'emergency_contact',
        'is_active',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'emergency_contact' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot([
                'relationship',
                'is_primary_guardian',
                'can_pickup',
                'financial_responsibility',
                'receive_notifications',
                'notes',
            ])
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimaryContact($query)
    {
        return $query->where('is_primary_contact', true);
    }

    public function scopeEmergencyContact($query)
    {
        return $query->where('emergency_contact', true);
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : '';
    }

    public function getEmailAttribute(): ?string
    {
        // Prefer guardian email field; fallback to linked user email
        return $this->attributes['email'] ?? ($this->user ? $this->user->email : null);
    }

    public function getStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getTotalPendingFeesAttribute(): float
    {
        // Sum pending for all linked students where guardian has financial responsibility
        $total = 0.0;
        $students = $this->students()
            ->wherePivot('financial_responsibility', true)
            ->get();

        foreach ($students as $student) {
            $total += (float) $student->getPendingFeesAmount();
        }

        return (float) number_format($total, 2, '.', '');
    }

    /**
     * Helper Methods
     */
    public function hasFinancialResponsibilityFor(int $studentId): bool
    {
        return $this->students()
            ->where('students.id', $studentId)
            ->wherePivot('financial_responsibility', true)
            ->exists();
    }

    public function canReceiveNotificationsFor(int $studentId): bool
    {
        return $this->students()
            ->where('students.id', $studentId)
            ->wherePivot('receive_notifications', true)
            ->exists();
    }

    /**
     * Fees helper returning a collection of fees for all linked students.
     * Note: This is not an Eloquent relationship due to pivot; use for convenience.
     */
    public function fees(): Collection
    {
        $studentIds = $this->students()->pluck('students.id');
        if ($studentIds->isEmpty()) {
            return collect();
        }

        return Fee::whereIn('student_id', $studentIds)->get();
    }
}

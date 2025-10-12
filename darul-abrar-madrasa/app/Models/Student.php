<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Student
 * 
 * @property int $id
 * @property int $user_id
 * @property int $class_id
 * @property string|null $roll_number
 * @property string|null $admission_number
 * @property \Carbon\Carbon $admission_date
 * @property string $father_name
 * @property string $mother_name
 * @property string $guardian_phone
 * @property string|null $guardian_email
 * @property string $address
 * @property \Carbon\Carbon $date_of_birth
 * @property string $gender
 * @property string|null $blood_group
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $user
 * @property-read ClassRoom $class
 * @property-read \Illuminate\Database\Eloquent\Collection|Attendance[] $attendances
 * @property-read \Illuminate\Database\Eloquent\Collection|Fee[] $fees
 * @property-read \Illuminate\Database\Eloquent\Collection|Result[] $results
 */
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
        // Deprecated: guardian direct fields kept for backward compatibility
        'father_name',       // Deprecated - use guardians relationship
        'mother_name',       // Deprecated - use guardians relationship
        'guardian_phone',    // Deprecated - use guardians relationship
        'guardian_email',    // Deprecated - use guardians relationship
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class that the student belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the attendances for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the fees for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    /**
     * Get the results for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Guardians relationship via pivot table guardian_student
     */
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
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
     * Primary guardian helper.
     */
    public function getPrimaryGuardian()
    {
        return $this->guardians()->wherePivot('is_primary_guardian', true)->first();
    }

    /**
     * Financially responsible guardian helper.
     */
    public function getFinancialGuardian()
    {
        return $this->guardians()->wherePivot('financial_responsibility', true)->first();
    }

    /**
     * Guardians who should receive notifications.
     */
    public function getGuardiansForNotification()
    {
        return $this->guardians()->wherePivot('receive_notifications', true)->get();
    }

    /**
     * Scope a query to only include active students.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include students in a specific class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $classId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope a query to search students by name, email, or admission number.
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
        })->orWhere('admission_number', 'like', "%{$search}%");
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
     * Get the full name of the student.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Get the email of the student.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    /**
     * Get the age of the student.
     *
     * @return int
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the status of the student.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the attendance rate for the student.
     *
     * @return float
     */
    public function getAttendanceRate()
    {
        $totalAttendance = $this->attendances()->count();
        
        if ($totalAttendance === 0) {
            return 0;
        }
        
        $presentCount = $this->attendances()
            ->where('status', 'present')
            ->count();
        
        return round(($presentCount / $totalAttendance) * 100, 2);
    }

    /**
     * Get the total pending fees amount for the student.
     *
     * @return float
     */
    public function getPendingFeesAmount()
    {
        // Consider waivers and installments via Fee model accessors
        return $this->fees()
            ->whereIn('status', ['unpaid', 'partial'])
            ->get()
            ->sum(function ($fee) {
                return max(0, (float)$fee->net_amount - (float)$fee->paid_amount);
            });
    }

    /**
     * Check if the student has any pending fees.
     *
     * @return bool
     */
    public function hasPendingFees()
    {
        return $this->fees()
            ->whereIn('status', ['unpaid', 'partial'])
            ->exists();
    }

    /**
     * Get active waivers for the student.
     */
    public function getActiveWaivers()
    {
        return FeeWaiver::query()->active()->forStudent($this->id)->get();
    }

    /**
     * Whether the student has any installment plan.
     */
    public function hasInstallmentPlan(): bool
    {
        return $this->fees()->whereHas('installments')->exists();
    }

    /**
     * Get the student's current GPA.
     *
     * @return float|null
     */
    public function getCurrentGpa()
    {
        $latestExam = $this->results()
            ->with('exam')
            ->latest('created_at')
            ->first();
        
        if (!$latestExam) {
            return null;
        }
        
        $examResults = $this->results()
            ->where('exam_id', $latestExam->exam_id)
            ->get();
        
        if ($examResults->isEmpty()) {
            return null;
        }
        
        return round($examResults->avg('gpa_point'), 2);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string|null $avatar
 * @property string|null $phone
 * @property bool $is_active
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read Teacher|null $teacher
 * @property-read Student|null $student
 * @property-read string $avatar_url
 * 
 * @deprecated The hasEffectiveRole() and hasAnyEffectiveRole() methods provide dual-check
 *             behavior during migration to Spatie. These will be removed after full data
 *             migration when all users have Spatie roles assigned. Use isAdmin(), isTeacher(),
 *             etc. for role-specific checks, or hasRole()/hasAnyRole() for Spatie-only checks.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the teacher record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }
    
    /**
     * Get the student record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the guardian record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * Get the accountant record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accountant()
    {
        return $this->hasOne(Accountant::class);
    }

    /**
     * Scope a query to filter by role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search by name or email.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    
    /**
     * Comment 1: Check if the user is an admin (dual-check: Spatie + legacy).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin';
    }
    
    /**
     * Comment 1: Check if the user is a teacher (dual-check: Spatie + legacy).
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('teacher') || $this->role === 'teacher';
    }
    
    /**
     * Comment 1: Check if the user is a student (dual-check: Spatie + legacy).
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student') || $this->role === 'student';
    }
    
    /**
     * Comment 1: Check if the user is a staff (dual-check: Spatie + legacy).
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff') || $this->role === 'staff';
    }

    /**
     * Comment 1: Check if the user is a guardian (dual-check: Spatie + legacy).
     *
     * @return bool
     */
    public function isGuardian(): bool
    {
        return $this->hasRole('guardian') || $this->role === 'guardian';
    }

    /**
     * Comment 1: Check if the user is an accountant (dual-check: Spatie + legacy).
     *
     * @return bool
     */
    public function isAccountant(): bool
    {
        return $this->hasRole('accountant') || $this->role === 'accountant';
    }

    /**
     * Comment 1: Check if the user has a corresponding record in the role-specific table.
     *
     * Uses effective role (Spatie + legacy fallback) to determine which record to check.
     *
     * @return bool True if the role record exists or is not required, false otherwise
     */
    public function hasRoleRecord(): bool
    {
        // Determine effective role (prefer Spatie roles during migration)
        $role = $this->role;
        if ($this->hasRole('teacher')) {
            $role = 'teacher';
        } elseif ($this->hasRole('student')) {
            $role = 'student';
        } elseif ($this->hasRole('guardian')) {
            $role = 'guardian';
        } elseif ($this->hasRole('accountant')) {
            $role = 'accountant';
        }
        
        return match($role) {
            'teacher' => $this->teacher()->exists(),
            'student' => $this->student()->exists(),
            'guardian' => $this->guardian()->exists(),
            'accountant' => $this->accountant()->exists(),
            'admin', 'staff' => true,
            default => false,
        };
    }

    /**
     * Comment 1: Get the role-specific record for this user.
     *
     * Uses effective role (Spatie + legacy fallback) to determine which record to return.
     *
     * @return Teacher|Student|Guardian|Accountant|null
     */
    public function getRoleRecordAttribute()
    {
        // Determine effective role (prefer Spatie roles during migration)
        $role = $this->role;
        if ($this->hasRole('teacher')) {
            $role = 'teacher';
        } elseif ($this->hasRole('student')) {
            $role = 'student';
        } elseif ($this->hasRole('guardian')) {
            $role = 'guardian';
        } elseif ($this->hasRole('accountant')) {
            $role = 'accountant';
        }
        
        return match($role) {
            'teacher' => $this->teacher,
            'student' => $this->student,
            'guardian' => $this->guardian,
            'accountant' => $this->accountant,
            default => null,
        };
    }

    /**
     * Comment 1: Check if user has effective role (Spatie + legacy fallback).
     *
     * This helper provides dual-check behavior during migration to Spatie permission system:
     * - Checks Spatie roles first
     * - Falls back to legacy string-based role column
     * - Supports arrays, collections, and pipe-delimited strings
     *
     * @deprecated Temporary method during migration. Will be removed after full Spatie migration.
     *             Use isAdmin(), isTeacher(), etc. for role-specific checks.
     *             Use hasRole()/hasAnyRole() for Spatie-only checks.
     *
     * @param string|array|\Illuminate\Support\Collection $roles
     * @return bool
     */
    public function hasEffectiveRole($roles): bool
    {
        // Convert pipe-delimited string to array
        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = explode('|', $roles);
        }

        // Ensure we have an array
        if (!is_array($roles) && !($roles instanceof \Illuminate\Support\Collection)) {
            $roles = [$roles];
        }

        // Check Spatie roles first
        if ($this->hasAnyRole($roles)) {
            return true;
        }

        // Fallback to legacy role column check
        foreach ($roles as $role) {
            if ($this->role === $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alias for hasEffectiveRole() for consistency with Spatie naming.
     *
     * @deprecated Temporary method during migration.
     *
     * @param string|array|\Illuminate\Support\Collection $roles
     * @return bool
     */
    public function hasAnyEffectiveRole($roles): bool
    {
        return $this->hasEffectiveRole($roles);
    }

    /**
     * Get the full avatar URL.
     *
     * Comment 5: Use effective role (Spatie-aware) instead of legacy column only
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        
        // Determine effective role using role detection methods
        if ($this->isAdmin()) {
            return asset('images/default-admin-avatar.png');
        } elseif ($this->isTeacher()) {
            return asset('images/default-teacher-avatar.png');
        } elseif ($this->isStudent()) {
            return asset('images/default-student-avatar.png');
        } elseif ($this->isGuardian()) {
            return asset('images/default-guardian-avatar.png');
        } elseif ($this->isAccountant()) {
            return asset('images/default-accountant-avatar.png');
        }
        
        return asset('images/default-avatar.png');
    }
}

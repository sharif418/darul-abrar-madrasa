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
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles {
        hasRole as spatieHasRole;
        hasAnyRole as spatieHasAnyRole;
    }

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    
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
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }
    
    /**
     * Check if the user is a student.
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
    
    /**
     * Check if the user is a staff.
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if the user is a guardian.
     *
     * Temporary dual-check during migration to Spatie permission system.
     *
     * @return bool
     */
    public function isGuardian(): bool
    {
        return $this->spatieHasRole('guardian') || $this->role === 'guardian';
    }

    /**
     * Check if the user is an accountant.
     *
     * Temporary dual-check during migration to Spatie permission system.
     *
     * @return bool
     */
    public function isAccountant(): bool
    {
        return $this->spatieHasRole('accountant') || $this->role === 'accountant';
    }

    /**
     * Check if the user has a corresponding record in the role-specific table.
     *
     * This method verifies that a user with a specific role has the required
     * record in the corresponding role table (teachers, students, guardians, accountants).
     * Admin and staff roles always return true as they don't require separate tables.
     * 
     * During migration to Spatie permission system, this method checks both the legacy
     * role column and Spatie roles to ensure accurate reporting.
     *
     * @return bool True if the role record exists or is not required, false otherwise
     */
    public function hasRoleRecord(): bool
    {
        // Determine effective role (prefer Spatie roles during migration)
        $role = $this->role;
        if ($this->spatieHasRole('teacher')) {
            $role = 'teacher';
        } elseif ($this->spatieHasRole('student')) {
            $role = 'student';
        } elseif ($this->spatieHasRole('guardian')) {
            $role = 'guardian';
        } elseif ($this->spatieHasRole('accountant')) {
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
     * Get the role-specific record for this user.
     *
     * This accessor returns the corresponding role model instance (Teacher, Student,
     * Guardian, or Accountant) based on the user's role. Returns null for admin/staff
     * roles or if no record exists.
     *
     * @return Teacher|Student|Guardian|Accountant|null
     */
    public function getRoleRecordAttribute()
    {
        return match($this->role) {
            'teacher' => $this->teacher,
            'student' => $this->student,
            'guardian' => $this->guardian,
            'accountant' => $this->accountant,
            default => null,
        };
    }

    /**
     * Check if user has specific role.
     *
     * Temporary dual-check during migration to Spatie permission system:
     * - Prefer Spatie roles
     * - Fallback to legacy string-based role column
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($roles): bool
    {
        // Support arrays/collections as Spatie may pass arrays internally
        if (is_array($roles) || $roles instanceof \Illuminate\Support\Collection) {
            foreach ($roles as $role) {
                if ($this->spatieHasRole($role) || $this->role === $role) {
                    return true;
                }
            }
            return false;
        }

        // Single role check
        if ($this->spatieHasRole($roles)) {
            return true;
        }

        // Fallback legacy role check (to be removed after full migration)
        return $this->role === $roles;
    }

    /**
     * Check if user has any of the specified roles.
     *
     * Temporary dual-check during migration to Spatie permission system:
     * - Prefer Spatie roles
     * - Fallback to legacy string-based role column
     * - Supports both array and pipe-delimited string inputs
     *
     * @param string|array|\Illuminate\Support\Collection $roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
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
        if ($this->spatieHasAnyRole($roles)) {
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
     * Get the full avatar URL.
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        
        // Return default avatar based on role
        return match($this->role) {
            'admin' => asset('images/default-admin-avatar.png'),
            'teacher' => asset('images/default-teacher-avatar.png'),
            'student' => asset('images/default-student-avatar.png'),
            default => asset('images/default-avatar.png'),
        };
    }
}

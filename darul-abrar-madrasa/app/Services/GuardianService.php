<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\User;
use App\Models\Student;
use App\Models\NotificationPreference;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

/**
 * GuardianService
 * 
 * Handles guardian creation, linking to students, and portal access setup.
 * Follows the service pattern established by ActivityLogService and FileUploadService.
 */
class GuardianService
{
    /**
     * Find existing guardian by phone or email.
     *
     * @param string|null $phone
     * @param string|null $email
     * @return Guardian|null
     */
    public function findExistingGuardian(?string $phone, ?string $email): ?Guardian
    {
        if (!$phone && !$email) {
            return null;
        }

        $query = Guardian::query();

        if ($phone) {
            $query->where('phone', $phone);
        }

        if ($email && !$phone) {
            $query->orWhere('email', $email);
        }

        return $query->first();
    }

    /**
     * Create a new guardian with user account.
     *
     * @param array $data Guardian data
     * @return Guardian
     */
    public function createGuardian(array $data): Guardian
    {
        return DB::transaction(function () use ($data) {
            // Create user account for guardian portal access
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? Str::random(12)), // Random password if not provided
                'role' => 'guardian',
                'phone' => $data['phone'],
                'is_active' => true,
            ]);

            // Create guardian record
            $guardian = Guardian::create([
                'user_id' => $user->id,
                'national_id' => $data['national_id'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'],
                'alternative_phone' => $data['alternative_phone'] ?? null,
                'email' => $data['email'],
                'relationship_type' => $data['relationship_type'] ?? 'other',
                'is_primary_contact' => $data['is_primary_contact'] ?? false,
                'emergency_contact' => $data['emergency_contact'] ?? false,
                'is_active' => true,
            ]);

            // Create default notification preferences
            $this->createDefaultNotificationPreferences($guardian->id);

            Log::info('Guardian created successfully', [
                'guardian_id' => $guardian->id,
                'user_id' => $user->id,
                'created_by' => auth()->id(),
            ]);

            return $guardian;
        });
    }

    /**
     * Link guardian to student with pivot data.
     *
     * @param Guardian $guardian
     * @param Student $student
     * @param array $pivotData
     * @return void
     */
    public function linkGuardianToStudent(Guardian $guardian, Student $student, array $pivotData): void
    {
        // Check if already linked
        if ($guardian->students()->where('student_id', $student->id)->exists()) {
            // Update pivot data
            $guardian->students()->updateExistingPivot($student->id, $pivotData);
            
            Log::info('Guardian-student link updated', [
                'guardian_id' => $guardian->id,
                'student_id' => $student->id,
            ]);
        } else {
            // Create new link
            $guardian->students()->attach($student->id, $pivotData);
            
            Log::info('Guardian linked to student', [
                'guardian_id' => $guardian->id,
                'student_id' => $student->id,
            ]);
        }
    }

    /**
     * Create or link guardian from student form data.
     *
     * @param array $guardianData
     * @param Student $student
     * @return Guardian
     */
    public function createOrLinkGuardian(array $guardianData, Student $student): Guardian
    {
        return DB::transaction(function () use ($guardianData, $student) {
            // Check if guardian exists
            $existingGuardian = null;
            
            if (!empty($guardianData['guardian_id'])) {
                // Existing guardian selected
                $existingGuardian = Guardian::find($guardianData['guardian_id']);
            } else {
                // Search by phone/email
                $existingGuardian = $this->findExistingGuardian(
                    $guardianData['phone'] ?? null,
                    $guardianData['email'] ?? null
                );
            }

            if ($existingGuardian) {
                // Link existing guardian
                $guardian = $existingGuardian;
            } else {
                // Create new guardian
                $guardian = $this->createGuardian($guardianData);
            }

            // Prepare pivot data
            $pivotData = [
                'relationship_type' => $guardianData['relationship_type'] ?? 'father',
                'is_primary_guardian' => $guardianData['is_primary_guardian'] ?? false,
                'can_pickup' => $guardianData['can_pickup'] ?? true,
                'financial_responsibility' => $guardianData['financial_responsibility'] ?? false,
                'receive_notifications' => $guardianData['receive_notifications'] ?? true,
                'notes' => $guardianData['notes'] ?? null,
            ];

            // Link to student
            $this->linkGuardianToStudent($guardian, $student, $pivotData);

            return $guardian;
        });
    }

    /**
     * Sync guardians for a student (add new, update existing, remove deleted).
     *
     * @param Student $student
     * @param array $guardiansData Array of guardian data
     * @return void
     */
    public function syncGuardiansForStudent(Student $student, array $guardiansData): void
    {
        DB::transaction(function () use ($student, $guardiansData) {
            $guardianIds = [];

            foreach ($guardiansData as $guardianData) {
                $guardian = $this->createOrLinkGuardian($guardianData, $student);
                $guardianIds[] = $guardian->id;
            }

            // Remove guardians not in the list (if any were deleted)
            $student->guardians()->whereNotIn('guardian_id', $guardianIds)->detach();

            Log::info('Guardians synced for student', [
                'student_id' => $student->id,
                'guardian_count' => count($guardianIds),
            ]);
        });
    }

    /**
     * Create default notification preferences for guardian.
     *
     * @param int $guardianId
     * @return void
     */
    protected function createDefaultNotificationPreferences(int $guardianId): void
    {
        try {
            // If notification preferences table doesn't exist, skip gracefully
            if (!Schema::hasTable('notification_preferences')) {
                Log::warning('Skipping default notification preferences creation: table missing', [
                    'guardian_id' => $guardianId,
                ]);
                return;
            }

            $notificationTypes = [
                Notification::TYPE_LOW_ATTENDANCE,
                Notification::TYPE_POOR_PERFORMANCE,
                Notification::TYPE_FEE_DUE,
                Notification::TYPE_EXAM_SCHEDULE,
                Notification::TYPE_RESULT_PUBLISHED,
            ];

            foreach ($notificationTypes as $type) {
                NotificationPreference::firstOrCreate(
                    [
                        'guardian_id' => $guardianId,
                        'notification_type' => $type,
                    ],
                    [
                        'email_enabled' => true,
                        'sms_enabled' => true,
                    ]
                );
            }

            Log::info('Default notification preferences created', [
                'guardian_id' => $guardianId,
            ]);
        } catch (\Throwable $e) {
            // Do not block student registration if notification setup has issues
            Log::error('Failed to create default notification preferences', [
                'guardian_id' => $guardianId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Search guardians by phone, email, or name.
     *
     * @param string $search
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchGuardians(string $search, int $limit = 10)
    {
        return Guardian::with('user')
            ->where(function ($query) use ($search) {
                $query->where('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('alternative_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Get guardian with students count.
     *
     * @param int $guardianId
     * @return Guardian
     */
    public function getGuardianWithStats(int $guardianId): Guardian
    {
        return Guardian::with(['user', 'students.user', 'students.class'])
            ->withCount('students')
            ->findOrFail($guardianId);
    }
}

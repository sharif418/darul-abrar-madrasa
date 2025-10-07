<?php

namespace App\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentRepository
{
    protected $student;
    protected FileUploadService $uploadService;

    public function __construct(Student $student, FileUploadService $uploadService)
    {
        $this->student = $student;
        $this->uploadService = $uploadService;
    }

    /**
     * Get all students with filters and pagination
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->student->with(['user', 'class.department']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        // Status filter
        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new student
     */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            // Create user first
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'student',
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Handle avatar upload via service
            if (isset($data['avatar']) && $data['avatar']) {
                $avatarPath = $this->uploadService->uploadAvatar($data['avatar']);
                $user->update(['avatar' => $avatarPath]);
            }

            // Generate admission number if not provided
            $admissionNumber = $data['admission_number'] ?? $this->generateStudentId();

            // Create student record
            $student = $this->student->create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'],
                'roll_number' => $data['roll_number'] ?? null,
                'admission_number' => $admissionNumber,
                'admission_date' => $data['admission_date'],
                'father_name' => $data['father_name'],
                'mother_name' => $data['mother_name'],
                'guardian_phone' => $data['guardian_phone'],
                'guardian_email' => $data['guardian_email'] ?? null,
                'address' => $data['address'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $student->load(['user', 'class.department']);
        });
    }

    /**
     * Update a student
     */
    public function update($student, $data)
    {
        return DB::transaction(function () use ($student, $data) {
            // Update user record
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $student->user->update($userData);

            // Handle avatar upload via service
            if (isset($data['avatar']) && $data['avatar']) {
                // Delete old avatar safely
                if ($student->user->avatar) {
                    $this->uploadService->deleteFile($student->user->avatar);
                }

                $avatarPath = $this->uploadService->uploadAvatar($data['avatar']);
                $student->user->update(['avatar' => $avatarPath]);
            }

            // Update student record
            $student->update([
                'class_id' => $data['class_id'],
                'roll_number' => $data['roll_number'] ?? null,
                'admission_number' => $data['admission_number'] ?? $student->admission_number,
                'admission_date' => $data['admission_date'],
                'father_name' => $data['father_name'],
                'mother_name' => $data['mother_name'],
                'guardian_phone' => $data['guardian_phone'],
                'guardian_email' => $data['guardian_email'] ?? null,
                'address' => $data['address'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $student->fresh(['user', 'class.department']);
        });
    }

    /**
     * Delete a student
     */
    public function delete($student)
    {
        return DB::transaction(function () use ($student) {
            // Delete avatar if exists using service
            if ($student->user->avatar) {
                $this->uploadService->deleteFile($student->user->avatar);
            }

            $user = $student->user;
            $student->delete();
            $user->delete();

            return true;
        });
    }

    /**
     * Bulk update class for a list of students.
     *
     * @param array<int> $studentIds
     * @param int $targetClassId
     * @return \Illuminate\Support\Collection<\App\Models\Student>
     */
    public function bulkUpdateClass(array $studentIds, int $targetClassId)
    {
        return DB::transaction(function () use ($studentIds, $targetClassId) {
            Student::whereIn('id', $studentIds)->update(['class_id' => $targetClassId]);
            return Student::whereIn('id', $studentIds)->get();
        });
    }

    /**
     * Bulk update active status for a list of students.
     *
     * @param array<int> $studentIds
     * @param bool $status
     * @return \Illuminate\Support\Collection<\App\Models\Student>
     */
    public function bulkUpdateStatus(array $studentIds, bool $status)
    {
        return DB::transaction(function () use ($studentIds, $status) {
            Student::whereIn('id', $studentIds)->update(['is_active' => $status]);
            return Student::whereIn('id', $studentIds)->get();
        });
    }

    /**
     * Get student with statistics
     */
    public function getWithStats($studentId)
    {
        $student = $this->student->with([
            'user',
            'class.department',
            'attendances' => function ($query) {
                $query->latest()->limit(10);
            },
            'results.exam',
            'results.subject',
            'fees' => function ($query) {
                $query->where('status', 'unpaid')->orWhere('status', 'partial');
            }
        ])->findOrFail($studentId);

        // Calculate attendance stats
        $totalAttendance = $student->attendances()->count();
        $presentCount = $student->attendances()->where('status', 'present')->count();
        $attendanceRate = $totalAttendance > 0 ? ($presentCount / $totalAttendance) * 100 : 0;

        // Calculate pending fees
        $pendingFees = $student->fees()
            ->where(function ($query) {
                $query->where('status', 'unpaid')
                      ->orWhere('status', 'partial');
            })
            ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

        $student->attendance_rate = round($attendanceRate, 2);
        $student->pending_fees_amount = $pendingFees;

        return $student;
    }

    /**
     * Generate unique student ID
     */
    private function generateStudentId()
    {
        $year = date('Y');
        $lastStudent = $this->student->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastStudent && $lastStudent->admission_number) {
            $lastNumber = (int) substr($lastStudent->admission_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'STD' . $year . $newNumber;
    }
}

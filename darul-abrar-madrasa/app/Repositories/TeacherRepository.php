<?php

namespace App\Repositories;

use App\Models\Teacher;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherRepository
{
    protected $teacher;
    protected FileUploadService $uploadService;

    public function __construct(Teacher $teacher, FileUploadService $uploadService)
    {
        $this->teacher = $teacher;
        $this->uploadService = $uploadService;
    }

    /**
     * Get all teachers with filters and pagination
     */
    public function getAllWithFilters($filters, $perPage = 15)
    {
        $query = $this->teacher->with(['user', 'department']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('designation', 'like', "%{$search}%");
            });
        }

        // Department filter
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Status filter
        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new teacher
     */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            // Create user first
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'teacher',
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Handle avatar upload via service
            if (isset($data['avatar']) && $data['avatar']) {
                $avatarPath = $this->uploadService->uploadAvatar($data['avatar']);
                $user->update(['avatar' => $avatarPath]);
            }

            // Create teacher record
            $teacher = $this->teacher->create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'designation' => $data['designation'],
                'qualification' => $data['qualification'],
                'joining_date' => $data['joining_date'],
                'address' => $data['address'],
                'salary' => $data['salary'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $teacher->load(['user', 'department']);
        });
    }

    /**
     * Update a teacher
     */
    public function update($teacher, $data)
    {
        return DB::transaction(function () use ($teacher, $data) {
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

            $teacher->user->update($userData);

            // Handle avatar upload via service
            if (isset($data['avatar']) && $data['avatar']) {
                // Delete old avatar safely
                if ($teacher->user->avatar) {
                    $this->uploadService->deleteFile($teacher->user->avatar);
                }

                $avatarPath = $this->uploadService->uploadAvatar($data['avatar']);
                $teacher->user->update(['avatar' => $avatarPath]);
            }

            // Update teacher record
            $teacher->update([
                'department_id' => $data['department_id'],
                'designation' => $data['designation'],
                'qualification' => $data['qualification'],
                'joining_date' => $data['joining_date'],
                'address' => $data['address'],
                'salary' => $data['salary'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $teacher->fresh(['user', 'department']);
        });
    }

    /**
     * Delete a teacher
     */
    public function delete($teacher)
    {
        return DB::transaction(function () use ($teacher) {
            // Delete avatar if exists using service
            if ($teacher->user->avatar) {
                $this->uploadService->deleteFile($teacher->user->avatar);
            }

            // Remove teacher from assigned subjects (set teacher_id to null)
            DB::table('subjects')
                ->where('teacher_id', $teacher->id)
                ->update(['teacher_id' => null]);

            $user = $teacher->user;
            $teacher->delete();
            $user->delete();

            return true;
        });
    }

    /**
     * Get teacher with assignments
     */
    public function getWithAssignments($teacherId)
    {
        $teacher = $this->teacher->with([
            'user',
            'department',
            'subjects.class',
            'subjects.class.students'
        ])->findOrFail($teacherId);

        // Get unique classes from assigned subjects
        $classes = $teacher->subjects->pluck('class')->unique('id');
        
        // Get upcoming exams for assigned classes
        $upcomingExams = DB::table('exams')
            ->whereIn('class_id', $classes->pluck('id'))
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        $teacher->assigned_classes = $classes;
        $teacher->upcoming_exams = $upcomingExams;

        return $teacher;
    }
}

<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance records (listing).
     */
    public function viewAny(User $user): bool
    {
        // Admins and teachers can browse attendance lists
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view a specific attendance record.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Admin can view any record
        if ($user->isAdmin()) {
            return true;
        }

        // Student can view own attendance
        if ($user->isStudent() && $user->student && (int)$user->student->id === (int)$attendance->student_id) {
            return true;
        }

        // Guardian linked to the student can view
        if ($user->isGuardian() && $user->guardian) {
            return $user->guardian->students()
                ->where('students.id', $attendance->student_id)
                ->exists();
        }

        // Teacher of the class can view
        if ($user->isTeacher() && $user->teacher) {
            try {
                $class = $attendance->class()->first();
                if ($class && (int)($class->teacher_id ?? 0) === (int)$user->teacher->id) {
                    return true;
                }
            } catch (\Throwable $e) {
                // deny if unable to resolve
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create attendance records.
     */
    public function create(User $user): bool
    {
        // Admins and teachers can create
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can update the attendance record.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Admin can update
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher of the class can update
        if ($user->isTeacher() && $user->teacher) {
            try {
                $class = $attendance->class()->first();
                if ($class && (int)($class->teacher_id ?? 0) === (int)$user->teacher->id) {
                    return true;
                }
            } catch (\Throwable $e) {
                // deny
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the attendance record.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Same rule as update
        return $this->update($user, $attendance);
    }

    /**
     * Determine whether the user can create attendance for a specific class (bulk).
     */
    public function createForClass(User $user, int $classId): bool
    {
        // Admin can create for any class
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher can create for own class
        if ($user->isTeacher() && $user->teacher) {
            try {
                $class = ClassRoom::find($classId);
                if ($class && (int)($class->teacher_id ?? 0) === (int)$user->teacher->id) {
                    return true;
                }
            } catch (\Throwable $e) {
                // deny
            }
        }

        return false;
    }

    /**
     * Determine whether the user can access their own attendance (student self only).
     */
    public function myAttendance(User $user): bool
    {
        return $user->isStudent() && (bool)$user->student;
    }
}

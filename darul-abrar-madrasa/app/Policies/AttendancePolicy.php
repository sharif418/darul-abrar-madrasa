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

        // Teacher who teaches any subject in the class can view
        if ($user->isTeacher() && $user->teacher) {
            // Check if teacher teaches any subject in this class OR is the class teacher
            return $user->teacher->subjects()
                ->where('class_id', $attendance->class_id)
                ->exists()
                || $user->teacher->isClassTeacherFor($attendance->class_id);
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

        // Teacher who teaches any subject in the class can update
        if ($user->isTeacher() && $user->teacher) {
            // Check if teacher teaches any subject in this class OR is the class teacher
            return $user->teacher->subjects()
                ->where('class_id', $attendance->class_id)
                ->exists()
                || $user->teacher->isClassTeacherFor($attendance->class_id);
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

        // Teacher who teaches any subject in the class can create attendance
        if ($user->isTeacher() && $user->teacher) {
            // Check if teacher teaches any subject in this class OR is the class teacher
            return $user->teacher->subjects()
                ->where('class_id', $classId)
                ->exists()
                || $user->teacher->isClassTeacherFor($classId);
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

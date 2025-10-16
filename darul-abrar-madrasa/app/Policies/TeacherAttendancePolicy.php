<?php

namespace App\Policies;

use App\Models\TeacherAttendance;
use App\Models\User;

class TeacherAttendancePolicy
{
    /**
     * Determine whether the user can view any teacher attendance records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the teacher attendance record.
     */
    public function view(User $user, TeacherAttendance $attendance): bool
    {
        // Admin can view any record
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher can view own attendance
        if ($user->isTeacher() && $user->teacher && $user->teacher->id === $attendance->teacher_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create teacher attendance records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the teacher attendance record.
     */
    public function update(User $user, TeacherAttendance $attendance): bool
    {
        // Admin can update any record
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher can update own attendance within same day
        if ($user->isTeacher() && 
            $user->teacher && 
            $user->teacher->id === $attendance->teacher_id && 
            $attendance->date->isToday()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the teacher attendance record.
     */
    public function delete(User $user, TeacherAttendance $attendance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view their own attendance history.
     */
    public function myAttendance(User $user): bool
    {
        return $user->isTeacher() && (bool)$user->teacher;
    }
}

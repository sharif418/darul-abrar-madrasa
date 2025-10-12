<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all students; teachers can view lists (scoped in controllers/repositories).
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        // Admin can view any student
        if ($user->isAdmin()) {
            return true;
        }

        // Student self
        if ($user->isStudent() && $user->student && (int)$user->student->id === (int)$student->id) {
            return true;
        }

        // Guardian linked to this student
        if ($user->isGuardian() && $user->guardian) {
            return $user->guardian->students()
                ->where('students.id', $student->id)
                ->exists();
        }

        // Teacher of the student's class (ClassRoom.teacher_id expected linkage)
        if ($user->isTeacher() && $user->teacher) {
            try {
                $class = $student->class()->first();
                if ($class && (int)($class->teacher_id ?? 0) === (int)$user->teacher->id) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Fallback: allow if teacher role but class relation not resolvable (to avoid hard failures)
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        // Admin only
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        // Admin only
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        // Admin only
        return $user->isAdmin();
    }

    /**
     * Bulk promote action authorization.
     */
    public function bulkPromote(User $user): bool
    {
        // Admin only
        return $user->isAdmin();
    }

    /**
     * Bulk transfer action authorization.
     */
    public function bulkTransfer(User $user): bool
    {
        // Admin only
        return $user->isAdmin();
    }

    /**
     * Bulk status update authorization.
     */
    public function bulkStatusUpdate(User $user): bool
    {
        // Admin only
        return $user->isAdmin();
    }
}

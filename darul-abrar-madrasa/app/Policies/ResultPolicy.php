<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\User;

class ResultPolicy
{
    /**
     * Determine whether the user can view any results (listing).
     */
    public function viewAny(User $user): bool
    {
        // Admins and teachers can browse results list (controller scopes the dataset).
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view a specific result.
     */
    public function view(User $user, Result $result): bool
    {
        // Admin can view any result
        if ($user->isAdmin()) {
            return true;
        }

        // Student can view own result
        if ($user->isStudent() && $user->student && (int)$user->student->id === (int)$result->student_id) {
            return true;
        }

        // Guardian linked to the student can view
        if ($user->isGuardian() && $user->guardian) {
            return $user->guardian->students()
                ->where('students.id', $result->student_id)
                ->exists();
        }

        // Comment 2: Teacher of the student's class (use class_teacher_id, not teacher_id)
        if ($user->isTeacher() && $user->teacher) {
            try {
                $student = $result->student()->with('class')->first();
                $class = $student?->class;
                if ($class && (int)($class->class_teacher_id ?? 0) === (int)$user->teacher->id) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Fail safe: deny if can't resolve relationships
                return false;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create results (bulk or marks entry).
     */
    public function create(User $user): bool
    {
        // Admins and teachers can create results (controllers enforce exam publish states).
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can update the result.
     */
    public function update(User $user, Result $result): bool
    {
        // Admin can update
        if ($user->isAdmin()) {
            return true;
        }

        // Comment 2: Teacher of the student's class can update (use class_teacher_id)
        if ($user->isTeacher() && $user->teacher) {
            try {
                $student = $result->student()->with('class')->first();
                $class = $student?->class;
                if ($class && (int)($class->class_teacher_id ?? 0) === (int)$user->teacher->id) {
                    return true;
                }
            } catch (\Throwable $e) {
                // deny on failure
                return false;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the result.
     */
    public function delete(User $user, Result $result): bool
    {
        // Same rule as update
        return $this->update($user, $result);
    }

    /**
     * Determine whether the user can publish results (toggle publish on exam/results).
     */
    public function publish(User $user): bool
    {
        // Admin only by default
        return $user->isAdmin();
    }
}

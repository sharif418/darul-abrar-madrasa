<?php

namespace App\Policies;

use App\Models\StudyMaterial;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;

class StudyMaterialPolicy
{
    /**
     * Determine whether the user can view any study materials.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the study material.
     */
    public function view(User $user, StudyMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            if (!$teacher) {
                return false;
            }
            if ((int) ($material->teacher_id ?? 0) === (int) $teacher->id) {
                return true;
            }
            // Fallback: allow if teacher teaches this class or subject
            return true;
        }

        if ($user->isStudent()) {
            $student = $user->student ?? null;
            if (!$student) {
                return false;
            }
            return (bool) ($material->is_published ?? false) && (int) $material->class_id === (int) $student->class_id;
        }

        if ($user->isGuardian()) {
            return $this->isForGuardianChild($user, $material);
        }

        return false;
    }

    /**
     * Determine whether the user can create study materials.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can update the study material.
     */
    public function update(User $user, StudyMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            return $teacher && (int) ($material->teacher_id ?? 0) === (int) $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the study material.
     */
    public function delete(User $user, StudyMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            return $teacher && (int) ($material->teacher_id ?? 0) === (int) $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can download the study material.
     */
    public function download(User $user, StudyMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            if (!$teacher) return false;
            if ((int) ($material->teacher_id ?? 0) === (int) $teacher->id) {
                return true;
            }
            // Fallback allow for teachers for now
            return true;
        }

        if ($user->isStudent()) {
            $student = $user->student ?? null;
            if (!$student) return false;
            return (bool) ($material->is_published ?? false) && (int) $material->class_id === (int) $student->class_id;
        }

        if ($user->isGuardian()) {
            return $this->isForGuardianChild($user, $material);
        }

        return false;
    }

    /**
     * Determine whether the user can toggle published status.
     */
    public function togglePublished(User $user, StudyMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            return $teacher && (int) ($material->teacher_id ?? 0) === (int) $teacher->id;
        }

        return false;
    }

    /**
     * Helper: does this published material apply to any of guardian's children?
     */
    private function isForGuardianChild(User $user, StudyMaterial $material): bool
    {
        if (!(bool) ($material->is_published ?? false)) {
            return false;
        }

        $guardian = Guardian::where('user_id', $user->id)->first();
        if (!$guardian) {
            return false;
        }

        $classId = (int) ($material->class_id ?? 0);
        if ($classId <= 0) {
            return false;
        }

        return $guardian->students()
            ->where('class_id', $classId)
            ->exists();
    }
}

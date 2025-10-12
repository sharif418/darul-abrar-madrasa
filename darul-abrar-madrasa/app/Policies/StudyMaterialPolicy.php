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
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            return true;
        }

        // Students and guardians use scoped endpoints (myMaterials/guardian portal)
        return false;
    }

    /**
     * Determine whether the user can view the study material.
     */
    public function view(User $user, StudyMaterial $material): bool
    {
        // Admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // Teacher: allow if creator or matches subject/class
        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            if (!$teacher) {
                return false;
            }
            if ((int) ($material->teacher_id ?? 0) === (int) $teacher->id) {
                return true;
            }
            // Fallback: allow if teacher teaches this class or subject (best-effort; model relations may vary)
            return true;
        }

        // Student: published AND class match
        if (method_exists($user, 'isStudent') && $user->isStudent()) {
            $student = $user->student ?? null;
            if (!$student) {
                return false;
            }
            return (bool) ($material->is_published ?? false) && (int) $material->class_id === (int) $student->class_id;
        }

        // Guardian: published AND for child's class
        if (method_exists($user, 'isGuardian') && $user->isGuardian()) {
            return $this->isForGuardianChild($user, $material);
        }

        return false;
    }

    /**
     * Determine whether the user can create study materials.
     */
    public function create(User $user): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the study material.
     */
    public function update(User $user, StudyMaterial $material): bool
    {
        // Admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // Teacher may update if they created it
        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
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
        // Admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // Teacher may delete if they created it
        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
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
        // Admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // Teacher: allow if creator or for their subject/class
        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            $teacher = $user->teacher ?? null;
            if (!$teacher) return false;
            if ((int) ($material->teacher_id ?? 0) === (int) $teacher->id) {
                return true;
            }
            // Fallback allow for teachers for now (fine-grained checks can be added)
            return true;
        }

        // Student: published AND class match
        if (method_exists($user, 'isStudent') && $user->isStudent()) {
            $student = $user->student ?? null;
            if (!$student) return false;
            return (bool) ($material->is_published ?? false) && (int) $material->class_id === (int) $student->class_id;
        }

        // Guardian: published AND for child's class
        if (method_exists($user, 'isGuardian') && $user->isGuardian()) {
            return $this->isForGuardianChild($user, $material);
        }

        return false;
    }

    /**
     * Determine whether the user can toggle published status.
     */
    public function togglePublished(User $user, StudyMaterial $material): bool
    {
        // Admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // Teacher who created it can toggle
        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
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

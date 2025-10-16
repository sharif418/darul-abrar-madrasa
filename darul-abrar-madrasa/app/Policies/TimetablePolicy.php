<?php

namespace App\Policies;

use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\User;

class TimetablePolicy
{
    /**
     * Determine whether the user can view any timetables.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the timetable.
     */
    public function view(User $user, Timetable $timetable): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can create timetables.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the timetable.
     */
    public function update(User $user, Timetable $timetable): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the timetable.
     */
    public function delete(User $user, Timetable $timetable): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create timetable entries.
     */
    public function createEntry(User $user, Timetable $timetable): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update timetable entries.
     */
    public function updateEntry(User $user, Timetable $timetable, TimetableEntry $entry): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete timetable entries.
     */
    public function deleteEntry(User $user, Timetable $timetable, TimetableEntry $entry): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view class timetable.
     */
    public function viewClassTimetable(User $user, Timetable $timetable, ClassRoom $class): bool
    {
        // Admin can view any class timetable
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher can view if they teach in the class or are class teacher
        if ($user->isTeacher() && $user->teacher) {
            return $user->teacher->subjects()->where('class_id', $class->id)->exists() 
                || $user->teacher->isClassTeacherFor($class->id);
        }

        return false;
    }

    /**
     * Determine whether the user can view teacher timetable.
     */
    public function viewTeacherTimetable(User $user, Timetable $timetable, Teacher $teacher): bool
    {
        // Admin can view any teacher timetable
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher can view own timetable
        if ($user->isTeacher() && $user->teacher && $user->teacher->id === $teacher->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view their own timetable.
     */
    public function viewMyTimetable(User $user): bool
    {
        return $user->isTeacher() && (bool)$user->teacher;
    }
}

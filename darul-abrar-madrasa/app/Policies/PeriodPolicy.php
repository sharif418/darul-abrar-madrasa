<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;

class PeriodPolicy
{
    /**
     * Determine whether the user can view any periods.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the period.
     */
    public function view(User $user, Period $period): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can create periods.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the period.
     */
    public function update(User $user, Period $period): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the period.
     */
    public function delete(User $user, Period $period): bool
    {
        return $user->isAdmin();
    }
}

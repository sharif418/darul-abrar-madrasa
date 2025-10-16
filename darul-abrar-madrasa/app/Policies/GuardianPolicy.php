<?php

namespace App\Policies;

use App\Models\Guardian;
use App\Models\User;

class GuardianPolicy
{
    /**
     * Determine whether the user can view any guardians.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the guardian.
     */
    public function view(User $user, Guardian $guardian): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Owner check: guardian user viewing their own profile
        return $user->id === $guardian->user_id;
    }

    /**
     * Determine whether the user can create guardians.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the guardian.
     */
    public function update(User $user, Guardian $guardian): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the guardian.
     */
    public function delete(User $user, Guardian $guardian): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the guardian.
     */
    public function restore(User $user, Guardian $guardian): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the guardian.
     */
    public function forceDelete(User $user, Guardian $guardian): bool
    {
        return $user->isAdmin();
    }
}

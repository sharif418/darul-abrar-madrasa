<?php

namespace App\Policies;

use App\Models\Accountant;
use App\Models\User;

class AccountantPolicy
{
    /**
     * Determine whether the user can view any accountants.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the accountant.
     */
    public function view(User $user, Accountant $accountant): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Owner check: accountant user viewing their own profile
        return $user->id === $accountant->user_id;
    }

    /**
     * Determine whether the user can create accountants.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the accountant.
     */
    public function update(User $user, Accountant $accountant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the accountant.
     */
    public function delete(User $user, Accountant $accountant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the accountant.
     */
    public function restore(User $user, Accountant $accountant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the accountant.
     */
    public function forceDelete(User $user, Accountant $accountant): bool
    {
        return $user->isAdmin();
    }
}

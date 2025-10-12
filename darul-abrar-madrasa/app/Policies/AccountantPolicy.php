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
        // Admins can view accountants list
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the accountant.
     */
    public function view(User $user, Accountant $accountant): bool
    {
        // Admin can view any accountant
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        // Owner check: accountant user viewing their own profile (if needed elsewhere)
        return $user->id === $accountant->user_id;
    }

    /**
     * Determine whether the user can create accountants.
     */
    public function create(User $user): bool
    {
        // Admin only
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the accountant.
     */
    public function update(User $user, Accountant $accountant): bool
    {
        // Admin only for admin-facing CRUD
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the accountant.
     */
    public function delete(User $user, Accountant $accountant): bool
    {
        // Admin only
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the accountant.
     */
    public function restore(User $user, Accountant $accountant): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the accountant.
     */
    public function forceDelete(User $user, Accountant $accountant): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }
}

<?php

namespace App\Policies;

use App\Models\Fee;
use App\Models\User;

class FeePolicy
{
    /**
     * Determine whether the user can view any fees (listing/search).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAccountant() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view a specific fee.
     */
    public function view(User $user, Fee $fee): bool
    {
        if ($user->isAdmin() || $user->isAccountant()) {
            return true;
        }
        
        if ($user->isTeacher()) {
            // Best-effort: allow teacher view; stricter checks can tie to class ownership.
            return true;
        }
        
        if ($user->isStudent()) {
            return (int) $user->student?->id === (int) $fee->student_id;
        }
        
        if ($user->isGuardian()) {
            $guardian = $user->guardian ?? null;
            if (!$guardian) {
                return false;
            }
            // Permit if guardian is linked to the student AND has financial responsibility
            $link = $guardian->students()->where('students.id', $fee->student_id)->first();
            return $link && (bool) $link->pivot->financial_responsibility;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create fees.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAccountant();
    }

    /**
     * Determine whether the user can update the fee.
     */
    public function update(User $user, Fee $fee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        
        if ($user->isAccountant()) {
            // Allow updates if not fully paid (or within an edit window if later enforced)
            return $fee->status !== 'paid';
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the fee.
     */
    public function delete(User $user, Fee $fee): bool
    {
        if ($user->isAdmin()) {
            return $fee->status !== 'paid';
        }
        
        if ($user->isAccountant()) {
            return $fee->status !== 'paid';
        }
        
        return false;
    }

    /**
     * Determine whether the user can record a payment on the fee.
     */
    public function recordPayment(User $user, Fee $fee): bool
    {
        return $user->isAdmin() || $user->isAccountant();
    }

    /**
     * Determine whether the user can apply a waiver to the fee.
     */
    public function applyWaiver(User $user, Fee $fee): bool
    {
        return $user->isAdmin() || $user->isAccountant();
    }

    /**
     * Determine whether the user can create an installment plan for the fee.
     */
    public function createInstallmentPlan(User $user, Fee $fee): bool
    {
        return $user->isAdmin() || $user->isAccountant();
    }
}

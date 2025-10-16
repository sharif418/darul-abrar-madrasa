<?php

namespace App\Policies;

use App\Models\FeeWaiver;
use App\Models\User;

class FeeWaiverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAccountant();
    }

    public function view(User $user, FeeWaiver $waiver): bool
    {
        if ($user->isAdmin() || $user->isAccountant()) {
            return true;
        }
        
        if ($user->isStudent()) {
            return (int) $user->student?->id === (int) $waiver->student_id;
        }
        
        if ($user->isGuardian()) {
            $guardian = $user->guardian ?? null;
            if (!$guardian) {
                return false;
            }
            return $guardian->students()->where('students.id', $waiver->student_id)->exists();
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAccountant();
    }

    public function update(User $user, FeeWaiver $waiver): bool
    {
        if ($waiver->status !== 'pending') {
            return false;
        }
        
        return $user->isAdmin() || $user->isAccountant();
    }

    public function delete(User $user, FeeWaiver $waiver): bool
    {
        if ($user->isAdmin()) {
            return in_array($waiver->status, ['pending', 'rejected'], true);
        }
        
        if ($user->isAccountant()) {
            return $waiver->status === 'pending';
        }
        
        return false;
    }

    public function approve(User $user, FeeWaiver $waiver): bool
    {
        if ($waiver->status !== 'pending') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAccountant()) {
            $accountant = $user->accountant ?? null;
            if (!$accountant) {
                return false;
            }
            if (!(bool) $accountant->can_approve_waivers) {
                return false;
            }
            // If max_waiver_amount is set, enforce
            if (!is_null($accountant->max_waiver_amount)) {
                return (float) $waiver->amount <= (float) $accountant->max_waiver_amount;
            }
            return true;
        }

        return false;
    }

    public function reject(User $user, FeeWaiver $waiver): bool
    {
        if ($waiver->status !== 'pending') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAccountant()) {
            $accountant = $user->accountant ?? null;
            return $accountant && (bool) $accountant->can_approve_waivers;
        }

        return false;
    }
}

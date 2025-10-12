<?php

namespace App\Policies;

use App\Models\FeeWaiver;
use App\Models\User;

class FeeWaiverPolicy
{
    public function viewAny(User $user): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }
        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            return true;
        }
        return false;
    }

    public function view(User $user, FeeWaiver $waiver): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }
        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            return true;
        }
        if (method_exists($user, 'isStudent') && $user->isStudent()) {
            return (int) $user->student?->id === (int) $waiver->student_id;
        }
        if (method_exists($user, 'isGuardian') && $user->isGuardian()) {
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
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }
        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            return true;
        }
        return false;
    }

    public function update(User $user, FeeWaiver $waiver): bool
    {
        if ($waiver->status !== 'pending') {
            return false;
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }
        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            return true;
        }
        return false;
    }

    public function delete(User $user, FeeWaiver $waiver): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return in_array($waiver->status, ['pending', 'rejected'], true);
        }
        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            return $waiver->status === 'pending';
        }
        return false;
    }

    public function approve(User $user, FeeWaiver $waiver): bool
    {
        if ($waiver->status !== 'pending') {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
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

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            $accountant = $user->accountant ?? null;
            return $accountant && (bool) $accountant->can_approve_waivers;
        }

        return false;
    }
}

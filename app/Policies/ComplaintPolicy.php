<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['resident', 'staff', 'admin'], true) && $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        return $user->is_active && ($user->role === 'admin'
            || $user->role === 'staff'
            || ($user->role === 'resident' && $complaint->resident_id === $user->id));
    }

    public function comment(User $user, Complaint $complaint): bool
    {
        return $this->view($user, $complaint);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['resident', 'staff', 'admin'], true);
    }

    public function manageStatus(User $user, Complaint $complaint): bool
    {
        return $user->is_active && in_array($user->role, ['staff', 'admin'], true);
    }

    public function resolve(User $user, Complaint $complaint): bool
    {
        return $user->is_active
            && $user->role === 'staff'
            && $complaint->status === 'approved';
    }

    public function submitReceipt(User $user, Complaint $complaint): bool
    {
        return $user->is_active
            && $user->role === 'resident'
            && $complaint->resident_id === $user->id
            && $complaint->status === 'approved'
            && $complaint->receipt_confirmed_at === null;
    }

    public function confirmReceipt(User $user, Complaint $complaint): bool
    {
        return $user->is_active
            && in_array($user->role, ['staff', 'admin'], true)
            && $complaint->status === 'approved'
            && $complaint->receipt_proof_path !== null
            && $complaint->receipt_confirmed_at === null;
    }

    public function manageSummons(User $user, Complaint $complaint): bool
    {
        return $user->is_active
            && in_array($user->role, ['staff', 'admin'], true)
            && $complaint->status === 'approved'
            && $complaint->receipt_confirmed_at !== null;
    }

    public function manageCaseRecords(User $user, Complaint $complaint): bool
    {
        return $user->is_active && in_array($user->role, ['staff', 'admin'], true);
    }

    public function viewEvidence(User $user, Complaint $complaint): bool
    {
        return $user->is_active && in_array($user->role, ['staff', 'admin'], true);
    }
}

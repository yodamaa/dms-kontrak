<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any contracts.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the contract.
     */
    public function view(User $user, Contract $contract)
    {
        return true;
    }

    /**
     * Determine whether the user can create contracts (Staff only).
     */
    public function create(User $user)
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the contract (Staff only, Pending status only).
     */
    public function update(User $user, Contract $contract)
    {
        return $user->isStaff() && $contract->canBeEdited();
    }

    /**
     * Determine whether the user can delete the contract (Supervisor only).
     */
    public function delete(User $user, Contract $contract)
    {
        return $user->isSupervisor();
    }

    /**
     * Determine whether the user can approve the contract (Supervisor only, Pending status only).
     */
    public function approve(User $user, Contract $contract)
    {
        return $user->isSupervisor() && $contract->canBeDecided();
    }

    /**
     * Determine whether the user can reject the contract (Supervisor only, Pending status only).
     */
    public function reject(User $user, Contract $contract)
    {
        return $user->isSupervisor() && $contract->canBeDecided();
    }
}

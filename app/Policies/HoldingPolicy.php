<?php

namespace App\Policies;

use App\Models\Holding;
use App\Models\User;

class HoldingPolicy
{
    /**
     * Determine if the user can view any holdings (only their own portfolio's holdings).
     */
    public function viewAny(User $user, User $requestedUser)
    {
        return $user->id === $requestedUser->id; // Only allow if it's their portfolio
    }

    /**
     * Determine if the user can view a specific holding.
     */
    public function view(User $user, Holding $holding)
    {
        // Ensure portfolio exists and matches the holding's portfolio_id
        return $user->portfolio !== null && $user->portfolio->id === $holding->portfolio_id;
    }

    /**
     * Determine if the user can create holdings.
     */
    public function create(User $user, User $requestedUser)
    {
        return $user->id === $requestedUser->id && $user->portfolio; // Can only add to their portfolio
    }

    /**
     * Determine if the user can update the holding.
     */
    public function update(User $user, Holding $holding)
    {
        return $user->portfolio !== null && $user->portfolio->id === $holding->portfolio_id;
    }

    /**
     * Determine if the user can delete the holding.
     */
    public function delete(User $user, Holding $holding)
    {
        return $user->portfolio !== null && $user->portfolio->id === $holding->portfolio_id;
    }
}
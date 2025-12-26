<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAddress;

class UserAddressPolicy
{
    /**
     * Determine if the user can view the address
     */
    public function view(User $user, UserAddress $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine if the user can update the address
     */
    public function update(User $user, UserAddress $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine if the user can delete the address
     */
    public function delete(User $user, UserAddress $address): bool
    {
        return $user->id === $address->user_id;
    }
}

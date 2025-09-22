<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WishlistShare;

class WishlistSharePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WishlistShare $wishlistShare): bool
    {
        return $user->id === $wishlistShare->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WishlistShare $wishlistShare): bool
    {
        return $user->id === $wishlistShare->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WishlistShare $wishlistShare): bool
    {
        return $user->id === $wishlistShare->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WishlistShare $wishlistShare): bool
    {
        return $user->id === $wishlistShare->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WishlistShare $wishlistShare): bool
    {
        return $user->id === $wishlistShare->user_id;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CartItems;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class CartItemsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CartItems');
    }

    public function view(AuthUser $authUser, CartItems $cartItems): bool
    {
        return $authUser->can('View:CartItems');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CartItems');
    }

    public function update(AuthUser $authUser, CartItems $cartItems): bool
    {
        return $authUser->can('Update:CartItems');
    }

    public function delete(AuthUser $authUser, CartItems $cartItems): bool
    {
        return $authUser->can('Delete:CartItems');
    }

    public function restore(AuthUser $authUser, CartItems $cartItems): bool
    {
        return $authUser->can('Restore:CartItems');
    }

    public function forceDelete(AuthUser $authUser, CartItems $cartItems): bool
    {
        return $authUser->can('ForceDelete:CartItems');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CartItems');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CartItems');
    }

    public function replicate(AuthUser $authUser, CartItems $cartItems): bool
    {
        return $authUser->can('Replicate:CartItems');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CartItems');
    }
}

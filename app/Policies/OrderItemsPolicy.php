<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderItems;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderItemsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderItems');
    }

    public function view(AuthUser $authUser, OrderItems $orderItems): bool
    {
        return $authUser->can('View:OrderItems');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderItems');
    }

    public function update(AuthUser $authUser, OrderItems $orderItems): bool
    {
        return $authUser->can('Update:OrderItems');
    }

    public function delete(AuthUser $authUser, OrderItems $orderItems): bool
    {
        return $authUser->can('Delete:OrderItems');
    }

    public function restore(AuthUser $authUser, OrderItems $orderItems): bool
    {
        return $authUser->can('Restore:OrderItems');
    }

    public function forceDelete(AuthUser $authUser, OrderItems $orderItems): bool
    {
        return $authUser->can('ForceDelete:OrderItems');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderItems');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderItems');
    }

    public function replicate(AuthUser $authUser, OrderItems $orderItems): bool
    {
        return $authUser->can('Replicate:OrderItems');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderItems');
    }

}
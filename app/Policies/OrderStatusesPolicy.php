<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderStatuses;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderStatusesPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderStatuses');
    }

    public function view(AuthUser $authUser, OrderStatuses $orderStatuses): bool
    {
        return $authUser->can('View:OrderStatuses');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderStatuses');
    }

    public function update(AuthUser $authUser, OrderStatuses $orderStatuses): bool
    {
        return $authUser->can('Update:OrderStatuses');
    }

    public function delete(AuthUser $authUser, OrderStatuses $orderStatuses): bool
    {
        return $authUser->can('Delete:OrderStatuses');
    }

    public function restore(AuthUser $authUser, OrderStatuses $orderStatuses): bool
    {
        return $authUser->can('Restore:OrderStatuses');
    }

    public function forceDelete(AuthUser $authUser, OrderStatuses $orderStatuses): bool
    {
        return $authUser->can('ForceDelete:OrderStatuses');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderStatuses');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderStatuses');
    }

    public function replicate(AuthUser $authUser, OrderStatuses $orderStatuses): bool
    {
        return $authUser->can('Replicate:OrderStatuses');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderStatuses');
    }

}
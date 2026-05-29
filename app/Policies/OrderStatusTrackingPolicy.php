<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderStatusTracking;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderStatusTrackingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderStatusTracking');
    }

    public function view(AuthUser $authUser, OrderStatusTracking $orderStatusTracking): bool
    {
        return $authUser->can('View:OrderStatusTracking');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderStatusTracking');
    }

    public function update(AuthUser $authUser, OrderStatusTracking $orderStatusTracking): bool
    {
        return $authUser->can('Update:OrderStatusTracking');
    }

    public function delete(AuthUser $authUser, OrderStatusTracking $orderStatusTracking): bool
    {
        return $authUser->can('Delete:OrderStatusTracking');
    }

    public function restore(AuthUser $authUser, OrderStatusTracking $orderStatusTracking): bool
    {
        return $authUser->can('Restore:OrderStatusTracking');
    }

    public function forceDelete(AuthUser $authUser, OrderStatusTracking $orderStatusTracking): bool
    {
        return $authUser->can('ForceDelete:OrderStatusTracking');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderStatusTracking');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderStatusTracking');
    }

    public function replicate(AuthUser $authUser, OrderStatusTracking $orderStatusTracking): bool
    {
        return $authUser->can('Replicate:OrderStatusTracking');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderStatusTracking');
    }

}
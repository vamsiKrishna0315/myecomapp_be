<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderCancellation;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderCancellationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderCancellation');
    }

    public function view(AuthUser $authUser, OrderCancellation $orderCancellation): bool
    {
        return $authUser->can('View:OrderCancellation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderCancellation');
    }

    public function update(AuthUser $authUser, OrderCancellation $orderCancellation): bool
    {
        return $authUser->can('Update:OrderCancellation');
    }

    public function delete(AuthUser $authUser, OrderCancellation $orderCancellation): bool
    {
        return $authUser->can('Delete:OrderCancellation');
    }

    public function restore(AuthUser $authUser, OrderCancellation $orderCancellation): bool
    {
        return $authUser->can('Restore:OrderCancellation');
    }

    public function forceDelete(AuthUser $authUser, OrderCancellation $orderCancellation): bool
    {
        return $authUser->can('ForceDelete:OrderCancellation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderCancellation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderCancellation');
    }

    public function replicate(AuthUser $authUser, OrderCancellation $orderCancellation): bool
    {
        return $authUser->can('Replicate:OrderCancellation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderCancellation');
    }

}
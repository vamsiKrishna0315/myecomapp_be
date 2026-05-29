<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderBilling;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderBillingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderBilling');
    }

    public function view(AuthUser $authUser, OrderBilling $orderBilling): bool
    {
        return $authUser->can('View:OrderBilling');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderBilling');
    }

    public function update(AuthUser $authUser, OrderBilling $orderBilling): bool
    {
        return $authUser->can('Update:OrderBilling');
    }

    public function delete(AuthUser $authUser, OrderBilling $orderBilling): bool
    {
        return $authUser->can('Delete:OrderBilling');
    }

    public function restore(AuthUser $authUser, OrderBilling $orderBilling): bool
    {
        return $authUser->can('Restore:OrderBilling');
    }

    public function forceDelete(AuthUser $authUser, OrderBilling $orderBilling): bool
    {
        return $authUser->can('ForceDelete:OrderBilling');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderBilling');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderBilling');
    }

    public function replicate(AuthUser $authUser, OrderBilling $orderBilling): bool
    {
        return $authUser->can('Replicate:OrderBilling');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderBilling');
    }

}
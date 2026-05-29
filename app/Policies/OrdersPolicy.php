<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Orders;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrdersPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Orders');
    }

    public function view(AuthUser $authUser, Orders $orders): bool
    {
        return $authUser->can('View:Orders');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Orders');
    }

    public function update(AuthUser $authUser, Orders $orders): bool
    {
        return $authUser->can('Update:Orders');
    }

    public function delete(AuthUser $authUser, Orders $orders): bool
    {
        return $authUser->can('Delete:Orders');
    }

    public function restore(AuthUser $authUser, Orders $orders): bool
    {
        return $authUser->can('Restore:Orders');
    }

    public function forceDelete(AuthUser $authUser, Orders $orders): bool
    {
        return $authUser->can('ForceDelete:Orders');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Orders');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Orders');
    }

    public function replicate(AuthUser $authUser, Orders $orders): bool
    {
        return $authUser->can('Replicate:Orders');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Orders');
    }

}
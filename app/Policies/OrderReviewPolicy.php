<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderReview;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderReviewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderReview');
    }

    public function view(AuthUser $authUser, OrderReview $orderReview): bool
    {
        return $authUser->can('View:OrderReview');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderReview');
    }

    public function update(AuthUser $authUser, OrderReview $orderReview): bool
    {
        return $authUser->can('Update:OrderReview');
    }

    public function delete(AuthUser $authUser, OrderReview $orderReview): bool
    {
        return $authUser->can('Delete:OrderReview');
    }

    public function restore(AuthUser $authUser, OrderReview $orderReview): bool
    {
        return $authUser->can('Restore:OrderReview');
    }

    public function forceDelete(AuthUser $authUser, OrderReview $orderReview): bool
    {
        return $authUser->can('ForceDelete:OrderReview');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderReview');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderReview');
    }

    public function replicate(AuthUser $authUser, OrderReview $orderReview): bool
    {
        return $authUser->can('Replicate:OrderReview');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderReview');
    }

}
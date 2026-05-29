<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductCut;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductCutPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductCut');
    }

    public function view(AuthUser $authUser, ProductCut $productCut): bool
    {
        return $authUser->can('View:ProductCut');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductCut');
    }

    public function update(AuthUser $authUser, ProductCut $productCut): bool
    {
        return $authUser->can('Update:ProductCut');
    }

    public function delete(AuthUser $authUser, ProductCut $productCut): bool
    {
        return $authUser->can('Delete:ProductCut');
    }

    public function restore(AuthUser $authUser, ProductCut $productCut): bool
    {
        return $authUser->can('Restore:ProductCut');
    }

    public function forceDelete(AuthUser $authUser, ProductCut $productCut): bool
    {
        return $authUser->can('ForceDelete:ProductCut');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductCut');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductCut');
    }

    public function replicate(AuthUser $authUser, ProductCut $productCut): bool
    {
        return $authUser->can('Replicate:ProductCut');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductCut');
    }

}
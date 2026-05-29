<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductGrade;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductGradePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductGrade');
    }

    public function view(AuthUser $authUser, ProductGrade $productGrade): bool
    {
        return $authUser->can('View:ProductGrade');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductGrade');
    }

    public function update(AuthUser $authUser, ProductGrade $productGrade): bool
    {
        return $authUser->can('Update:ProductGrade');
    }

    public function delete(AuthUser $authUser, ProductGrade $productGrade): bool
    {
        return $authUser->can('Delete:ProductGrade');
    }

    public function restore(AuthUser $authUser, ProductGrade $productGrade): bool
    {
        return $authUser->can('Restore:ProductGrade');
    }

    public function forceDelete(AuthUser $authUser, ProductGrade $productGrade): bool
    {
        return $authUser->can('ForceDelete:ProductGrade');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductGrade');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductGrade');
    }

    public function replicate(AuthUser $authUser, ProductGrade $productGrade): bool
    {
        return $authUser->can('Replicate:ProductGrade');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductGrade');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CutType;
use Illuminate\Auth\Access\HandlesAuthorization;

class CutTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CutType');
    }

    public function view(AuthUser $authUser, CutType $cutType): bool
    {
        return $authUser->can('View:CutType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CutType');
    }

    public function update(AuthUser $authUser, CutType $cutType): bool
    {
        return $authUser->can('Update:CutType');
    }

    public function delete(AuthUser $authUser, CutType $cutType): bool
    {
        return $authUser->can('Delete:CutType');
    }

    public function restore(AuthUser $authUser, CutType $cutType): bool
    {
        return $authUser->can('Restore:CutType');
    }

    public function forceDelete(AuthUser $authUser, CutType $cutType): bool
    {
        return $authUser->can('ForceDelete:CutType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CutType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CutType');
    }

    public function replicate(AuthUser $authUser, CutType $cutType): bool
    {
        return $authUser->can('Replicate:CutType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CutType');
    }

}
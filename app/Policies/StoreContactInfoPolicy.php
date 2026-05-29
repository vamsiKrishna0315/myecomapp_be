<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StoreContactInfo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class StoreContactInfoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StoreContactInfo');
    }

    public function view(AuthUser $authUser, StoreContactInfo $storeContactInfo): bool
    {
        return $authUser->can('View:StoreContactInfo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StoreContactInfo');
    }

    public function update(AuthUser $authUser, StoreContactInfo $storeContactInfo): bool
    {
        return $authUser->can('Update:StoreContactInfo');
    }

    public function delete(AuthUser $authUser, StoreContactInfo $storeContactInfo): bool
    {
        return $authUser->can('Delete:StoreContactInfo');
    }

    public function restore(AuthUser $authUser, StoreContactInfo $storeContactInfo): bool
    {
        return $authUser->can('Restore:StoreContactInfo');
    }

    public function forceDelete(AuthUser $authUser, StoreContactInfo $storeContactInfo): bool
    {
        return $authUser->can('ForceDelete:StoreContactInfo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StoreContactInfo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StoreContactInfo');
    }

    public function replicate(AuthUser $authUser, StoreContactInfo $storeContactInfo): bool
    {
        return $authUser->can('Replicate:StoreContactInfo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StoreContactInfo');
    }
}

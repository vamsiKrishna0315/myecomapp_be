<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FlashBanner;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class FlashBannerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FlashBanner');
    }

    public function view(AuthUser $authUser, FlashBanner $flashBanner): bool
    {
        return $authUser->can('View:FlashBanner');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FlashBanner');
    }

    public function update(AuthUser $authUser, FlashBanner $flashBanner): bool
    {
        return $authUser->can('Update:FlashBanner');
    }

    public function delete(AuthUser $authUser, FlashBanner $flashBanner): bool
    {
        return $authUser->can('Delete:FlashBanner');
    }

    public function restore(AuthUser $authUser, FlashBanner $flashBanner): bool
    {
        return $authUser->can('Restore:FlashBanner');
    }

    public function forceDelete(AuthUser $authUser, FlashBanner $flashBanner): bool
    {
        return $authUser->can('ForceDelete:FlashBanner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FlashBanner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FlashBanner');
    }

    public function replicate(AuthUser $authUser, FlashBanner $flashBanner): bool
    {
        return $authUser->can('Replicate:FlashBanner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FlashBanner');
    }
}

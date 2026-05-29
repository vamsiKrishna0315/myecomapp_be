<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WhyUs;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhyUsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhyUs');
    }

    public function view(AuthUser $authUser, WhyUs $whyUs): bool
    {
        return $authUser->can('View:WhyUs');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WhyUs');
    }

    public function update(AuthUser $authUser, WhyUs $whyUs): bool
    {
        return $authUser->can('Update:WhyUs');
    }

    public function delete(AuthUser $authUser, WhyUs $whyUs): bool
    {
        return $authUser->can('Delete:WhyUs');
    }

    public function restore(AuthUser $authUser, WhyUs $whyUs): bool
    {
        return $authUser->can('Restore:WhyUs');
    }

    public function forceDelete(AuthUser $authUser, WhyUs $whyUs): bool
    {
        return $authUser->can('ForceDelete:WhyUs');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WhyUs');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WhyUs');
    }

    public function replicate(AuthUser $authUser, WhyUs $whyUs): bool
    {
        return $authUser->can('Replicate:WhyUs');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WhyUs');
    }

}
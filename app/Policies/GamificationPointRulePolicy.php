<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GamificationPointRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamificationPointRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GamificationPointRule');
    }

    public function view(AuthUser $authUser, GamificationPointRule $gamificationPointRule): bool
    {
        return $authUser->can('View:GamificationPointRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GamificationPointRule');
    }

    public function update(AuthUser $authUser, GamificationPointRule $gamificationPointRule): bool
    {
        return $authUser->can('Update:GamificationPointRule');
    }

    public function delete(AuthUser $authUser, GamificationPointRule $gamificationPointRule): bool
    {
        return $authUser->can('Delete:GamificationPointRule');
    }

    public function restore(AuthUser $authUser, GamificationPointRule $gamificationPointRule): bool
    {
        return $authUser->can('Restore:GamificationPointRule');
    }

    public function forceDelete(AuthUser $authUser, GamificationPointRule $gamificationPointRule): bool
    {
        return $authUser->can('ForceDelete:GamificationPointRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GamificationPointRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GamificationPointRule');
    }

    public function replicate(AuthUser $authUser, GamificationPointRule $gamificationPointRule): bool
    {
        return $authUser->can('Replicate:GamificationPointRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GamificationPointRule');
    }

}
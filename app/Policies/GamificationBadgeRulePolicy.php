<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GamificationBadgeRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamificationBadgeRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GamificationBadgeRule');
    }

    public function view(AuthUser $authUser, GamificationBadgeRule $gamificationBadgeRule): bool
    {
        return $authUser->can('View:GamificationBadgeRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GamificationBadgeRule');
    }

    public function update(AuthUser $authUser, GamificationBadgeRule $gamificationBadgeRule): bool
    {
        return $authUser->can('Update:GamificationBadgeRule');
    }

    public function delete(AuthUser $authUser, GamificationBadgeRule $gamificationBadgeRule): bool
    {
        return $authUser->can('Delete:GamificationBadgeRule');
    }

    public function restore(AuthUser $authUser, GamificationBadgeRule $gamificationBadgeRule): bool
    {
        return $authUser->can('Restore:GamificationBadgeRule');
    }

    public function forceDelete(AuthUser $authUser, GamificationBadgeRule $gamificationBadgeRule): bool
    {
        return $authUser->can('ForceDelete:GamificationBadgeRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GamificationBadgeRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GamificationBadgeRule');
    }

    public function replicate(AuthUser $authUser, GamificationBadgeRule $gamificationBadgeRule): bool
    {
        return $authUser->can('Replicate:GamificationBadgeRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GamificationBadgeRule');
    }

}
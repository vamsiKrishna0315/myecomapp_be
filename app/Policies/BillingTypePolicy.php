<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BillingType;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillingTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BillingType');
    }

    public function view(AuthUser $authUser, BillingType $billingType): bool
    {
        return $authUser->can('View:BillingType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BillingType');
    }

    public function update(AuthUser $authUser, BillingType $billingType): bool
    {
        return $authUser->can('Update:BillingType');
    }

    public function delete(AuthUser $authUser, BillingType $billingType): bool
    {
        return $authUser->can('Delete:BillingType');
    }

    public function restore(AuthUser $authUser, BillingType $billingType): bool
    {
        return $authUser->can('Restore:BillingType');
    }

    public function forceDelete(AuthUser $authUser, BillingType $billingType): bool
    {
        return $authUser->can('ForceDelete:BillingType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BillingType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BillingType');
    }

    public function replicate(AuthUser $authUser, BillingType $billingType): bool
    {
        return $authUser->can('Replicate:BillingType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BillingType');
    }

}
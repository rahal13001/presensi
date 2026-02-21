<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LeaveQuota;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveQuotaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LeaveQuota');
    }

    public function view(AuthUser $authUser, LeaveQuota $leaveQuota): bool
    {
        return $authUser->can('View:LeaveQuota');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LeaveQuota');
    }

    public function update(AuthUser $authUser, LeaveQuota $leaveQuota): bool
    {
        return $authUser->can('Update:LeaveQuota');
    }

    public function delete(AuthUser $authUser, LeaveQuota $leaveQuota): bool
    {
        return $authUser->can('Delete:LeaveQuota');
    }

    public function restore(AuthUser $authUser, LeaveQuota $leaveQuota): bool
    {
        return $authUser->can('Restore:LeaveQuota');
    }

    public function forceDelete(AuthUser $authUser, LeaveQuota $leaveQuota): bool
    {
        return $authUser->can('ForceDelete:LeaveQuota');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LeaveQuota');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LeaveQuota');
    }

    public function replicate(AuthUser $authUser, LeaveQuota $leaveQuota): bool
    {
        return $authUser->can('Replicate:LeaveQuota');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LeaveQuota');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Shiftschedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftschedulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Shiftschedule');
    }

    public function view(AuthUser $authUser, Shiftschedule $shiftschedule): bool
    {
        return $authUser->can('View:Shiftschedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Shiftschedule');
    }

    public function update(AuthUser $authUser, Shiftschedule $shiftschedule): bool
    {
        return $authUser->can('Update:Shiftschedule');
    }

    public function delete(AuthUser $authUser, Shiftschedule $shiftschedule): bool
    {
        return $authUser->can('Delete:Shiftschedule');
    }

    public function restore(AuthUser $authUser, Shiftschedule $shiftschedule): bool
    {
        return $authUser->can('Restore:Shiftschedule');
    }

    public function forceDelete(AuthUser $authUser, Shiftschedule $shiftschedule): bool
    {
        return $authUser->can('ForceDelete:Shiftschedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Shiftschedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Shiftschedule');
    }

    public function replicate(AuthUser $authUser, Shiftschedule $shiftschedule): bool
    {
        return $authUser->can('Replicate:Shiftschedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Shiftschedule');
    }

}
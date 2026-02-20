<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Typeofleave;
use Illuminate\Auth\Access\HandlesAuthorization;

class TypeofleavePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Typeofleave');
    }

    public function view(AuthUser $authUser, Typeofleave $typeofleave): bool
    {
        return $authUser->can('View:Typeofleave');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Typeofleave');
    }

    public function update(AuthUser $authUser, Typeofleave $typeofleave): bool
    {
        return $authUser->can('Update:Typeofleave');
    }

    public function delete(AuthUser $authUser, Typeofleave $typeofleave): bool
    {
        return $authUser->can('Delete:Typeofleave');
    }

    public function restore(AuthUser $authUser, Typeofleave $typeofleave): bool
    {
        return $authUser->can('Restore:Typeofleave');
    }

    public function forceDelete(AuthUser $authUser, Typeofleave $typeofleave): bool
    {
        return $authUser->can('ForceDelete:Typeofleave');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Typeofleave');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Typeofleave');
    }

    public function replicate(AuthUser $authUser, Typeofleave $typeofleave): bool
    {
        return $authUser->can('Replicate:Typeofleave');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Typeofleave');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Scope;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScopePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Scope');
    }

    public function view(AuthUser $authUser, Scope $scope): bool
    {
        return $authUser->can('View:Scope');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Scope');
    }

    public function update(AuthUser $authUser, Scope $scope): bool
    {
        return $authUser->can('Update:Scope');
    }

    public function delete(AuthUser $authUser, Scope $scope): bool
    {
        return $authUser->can('Delete:Scope');
    }

    public function restore(AuthUser $authUser, Scope $scope): bool
    {
        return $authUser->can('Restore:Scope');
    }

    public function forceDelete(AuthUser $authUser, Scope $scope): bool
    {
        return $authUser->can('ForceDelete:Scope');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Scope');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Scope');
    }

    public function replicate(AuthUser $authUser, Scope $scope): bool
    {
        return $authUser->can('Replicate:Scope');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Scope');
    }

}
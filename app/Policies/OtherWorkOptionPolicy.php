<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OtherWorkOption;
use Illuminate\Auth\Access\HandlesAuthorization;

class OtherWorkOptionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OtherWorkOption');
    }

    public function view(AuthUser $authUser, OtherWorkOption $otherWorkOption): bool
    {
        return $authUser->can('View:OtherWorkOption');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OtherWorkOption');
    }

    public function update(AuthUser $authUser, OtherWorkOption $otherWorkOption): bool
    {
        return $authUser->can('Update:OtherWorkOption');
    }

    public function delete(AuthUser $authUser, OtherWorkOption $otherWorkOption): bool
    {
        return $authUser->can('Delete:OtherWorkOption');
    }

    public function restore(AuthUser $authUser, OtherWorkOption $otherWorkOption): bool
    {
        return $authUser->can('Restore:OtherWorkOption');
    }

    public function forceDelete(AuthUser $authUser, OtherWorkOption $otherWorkOption): bool
    {
        return $authUser->can('ForceDelete:OtherWorkOption');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OtherWorkOption');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OtherWorkOption');
    }

    public function replicate(AuthUser $authUser, OtherWorkOption $otherWorkOption): bool
    {
        return $authUser->can('Replicate:OtherWorkOption');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OtherWorkOption');
    }

}
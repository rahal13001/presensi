<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Monthlyreport;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthlyreportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Monthlyreport');
    }

    public function view(AuthUser $authUser, Monthlyreport $monthlyreport): bool
    {
        return $authUser->can('View:Monthlyreport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Monthlyreport');
    }

    public function update(AuthUser $authUser, Monthlyreport $monthlyreport): bool
    {
        return $authUser->can('Update:Monthlyreport');
    }

    public function delete(AuthUser $authUser, Monthlyreport $monthlyreport): bool
    {
        return $authUser->can('Delete:Monthlyreport');
    }

    public function restore(AuthUser $authUser, Monthlyreport $monthlyreport): bool
    {
        return $authUser->can('Restore:Monthlyreport');
    }

    public function forceDelete(AuthUser $authUser, Monthlyreport $monthlyreport): bool
    {
        return $authUser->can('ForceDelete:Monthlyreport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Monthlyreport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Monthlyreport');
    }

    public function replicate(AuthUser $authUser, Monthlyreport $monthlyreport): bool
    {
        return $authUser->can('Replicate:Monthlyreport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Monthlyreport');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MonthlyReportV2;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthlyReportV2Policy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonthlyReportV2');
    }

    public function view(AuthUser $authUser, MonthlyReportV2 $monthlyReportV2): bool
    {
        return $authUser->can('View:MonthlyReportV2');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonthlyReportV2');
    }

    public function update(AuthUser $authUser, MonthlyReportV2 $monthlyReportV2): bool
    {
        return $authUser->can('Update:MonthlyReportV2');
    }

    public function delete(AuthUser $authUser, MonthlyReportV2 $monthlyReportV2): bool
    {
        return $authUser->can('Delete:MonthlyReportV2');
    }

    public function restore(AuthUser $authUser, MonthlyReportV2 $monthlyReportV2): bool
    {
        return $authUser->can('Restore:MonthlyReportV2');
    }

    public function forceDelete(AuthUser $authUser, MonthlyReportV2 $monthlyReportV2): bool
    {
        return $authUser->can('ForceDelete:MonthlyReportV2');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonthlyReportV2');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonthlyReportV2');
    }

    public function replicate(AuthUser $authUser, MonthlyReportV2 $monthlyReportV2): bool
    {
        return $authUser->can('Replicate:MonthlyReportV2');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonthlyReportV2');
    }

}
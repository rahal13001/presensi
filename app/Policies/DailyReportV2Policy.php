<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DailyReportV2;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportV2Policy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DailyReportV2');
    }

    public function view(AuthUser $authUser, DailyReportV2 $dailyReportV2): bool
    {
        return $authUser->can('View:DailyReportV2');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DailyReportV2');
    }

    public function update(AuthUser $authUser, DailyReportV2 $dailyReportV2): bool
    {
        return $authUser->can('Update:DailyReportV2');
    }

    public function delete(AuthUser $authUser, DailyReportV2 $dailyReportV2): bool
    {
        return $authUser->can('Delete:DailyReportV2');
    }

    public function restore(AuthUser $authUser, DailyReportV2 $dailyReportV2): bool
    {
        return $authUser->can('Restore:DailyReportV2');
    }

    public function forceDelete(AuthUser $authUser, DailyReportV2 $dailyReportV2): bool
    {
        return $authUser->can('ForceDelete:DailyReportV2');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DailyReportV2');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DailyReportV2');
    }

    public function replicate(AuthUser $authUser, DailyReportV2 $dailyReportV2): bool
    {
        return $authUser->can('Replicate:DailyReportV2');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DailyReportV2');
    }

}
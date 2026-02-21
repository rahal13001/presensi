<?php

namespace App\Filament\Resources\LeaveQuotaResource\Pages;

use App\Filament\Resources\LeaveQuotaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeaveQuotas extends ListRecords
{
    protected static string $resource = LeaveQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

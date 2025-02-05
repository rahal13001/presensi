<?php

namespace App\Filament\Resources\ShiftscheduleResource\Pages;

use App\Filament\Resources\ShiftscheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftschedules extends ListRecords
{
    protected static string $resource = ShiftscheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

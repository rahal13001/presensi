<?php

namespace App\Filament\Resources\ShiftscheduleResource\Pages;

use App\Filament\Resources\ShiftscheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShiftschedule extends EditRecord
{
    protected static string $resource = ShiftscheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

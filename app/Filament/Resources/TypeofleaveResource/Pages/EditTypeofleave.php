<?php

namespace App\Filament\Resources\TypeofleaveResource\Pages;

use App\Filament\Resources\TypeofleaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeofleave extends EditRecord
{
    protected static string $resource = TypeofleaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

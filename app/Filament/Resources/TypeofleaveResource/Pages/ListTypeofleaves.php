<?php

namespace App\Filament\Resources\TypeofleaveResource\Pages;

use App\Filament\Resources\TypeofleaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeofleaves extends ListRecords
{
    protected static string $resource = TypeofleaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

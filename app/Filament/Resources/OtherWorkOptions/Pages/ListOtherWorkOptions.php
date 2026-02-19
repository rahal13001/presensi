<?php

namespace App\Filament\Resources\OtherWorkOptions\Pages;

use App\Filament\Resources\OtherWorkOptions\OtherWorkOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOtherWorkOptions extends ListRecords
{
    protected static string $resource = OtherWorkOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

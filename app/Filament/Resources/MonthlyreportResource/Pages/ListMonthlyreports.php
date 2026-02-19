<?php

namespace App\Filament\Resources\MonthlyreportResource\Pages;

use App\Filament\Resources\MonthlyreportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyreports extends ListRecords
{
    protected static string $resource = MonthlyreportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

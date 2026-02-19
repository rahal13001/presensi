<?php

namespace App\Filament\Resources\MonthlyReportV2\Pages;

use App\Filament\Resources\MonthlyReportV2\MonthlyReportV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyReportsV2 extends ListRecords
{
    protected static string $resource = MonthlyReportV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

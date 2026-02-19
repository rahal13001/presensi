<?php

namespace App\Filament\Resources\DailyReportV2\Pages;

use App\Filament\Resources\DailyReportV2\DailyReportV2Resource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyReportsV2 extends ListRecords
{
    protected static string $resource = DailyReportV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

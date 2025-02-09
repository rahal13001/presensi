<?php

namespace App\Filament\Resources\MonthlyreportResource\Pages;

use App\Filament\Resources\MonthlyreportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyreport extends EditRecord
{
    protected static string $resource = MonthlyreportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

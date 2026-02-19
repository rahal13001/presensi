<?php

namespace App\Filament\Resources\MonthlyReportV2\Pages;

use App\Filament\Resources\MonthlyReportV2\MonthlyReportV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMonthlyReportV2 extends ViewRecord
{
    protected static string $resource = MonthlyReportV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'draft')
                ->url(fn () => route('monthly-report-v2.pdf', $this->record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}

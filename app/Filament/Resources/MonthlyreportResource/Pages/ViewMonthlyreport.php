<?php

namespace App\Filament\Resources\MonthlyreportResource\Pages;

use App\Filament\Resources\MonthlyreportResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMonthlyreport extends ViewRecord
{
    protected static string $resource = MonthlyreportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square'),

            Action::make('Export PDF')
                    ->label('Laporan Tugas Harian')
                    ->icon('heroicon-o-folder-arrow-down')
                    ->url(fn ($record) => route('pdftugasharian', $record->id))
                    ->button()
                    ->color('info'),

            Action::make('Data Presensi')
                    ->label('Data Presensi')
                    ->icon('heroicon-o-arrow-down-on-square-stack')
                    ->url(fn ($record) => route('pdfdatapresensi', $record->id))
                    ->button()
                    ->color('success'),

            Action::make('Laporan Presensi')
                    ->label('Laporan Presensi')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->url(fn ($record) => route('pdflaporanpresensi', $record->id))
                    ->button()
                    ->color('brown'),


        ];
    }
}

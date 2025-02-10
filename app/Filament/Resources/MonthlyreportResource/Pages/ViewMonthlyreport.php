<?php

namespace App\Filament\Resources\MonthlyreportResource\Pages;

use Filament\Actions;
use App\Models\Attendance;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\MonthlyreportResource;

class ViewMonthlyreport extends ViewRecord
{
    protected static string $resource = MonthlyreportResource::class;

    protected function getHeaderActions(): array
    {
        $hasAttendanceData = Attendance::where('user_id', $this->record->user_id)->exists();

        $actions = [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square'),
        ];

        if ($hasAttendanceData) {
            $actions = array_merge($actions, [
                Action::make('Export PDF')
                    ->label('Laporan Tugas Harian')
                    ->icon('heroicon-o-folder-arrow-down')
                    ->url(fn ($record) => route('pdftugasharian', $record->id), '_blank')
                    ->button()
                    ->color('info'),

                Action::make('Data Presensi')
                    ->label('Data Presensi')
                    ->icon('heroicon-o-arrow-down-on-square-stack')
                    ->url(fn ($record) => route('pdfdatapresensi', $record->id), '_blank')
                    ->button()
                    ->color('success'),

                Action::make('Laporan Presensi')
                    ->label('Laporan Presensi')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->url(fn ($record) => route('pdflaporanpresensi', $record->id), '_blank')
                    ->button()
                    ->color('brown'),
            ]);
        }


        return $actions;
    }
}

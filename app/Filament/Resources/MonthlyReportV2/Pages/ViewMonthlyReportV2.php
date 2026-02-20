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
            Actions\Action::make('submit_for_review')
                ->label('Kirim untuk Review')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan untuk Review')
                ->modalDescription('Apakah Anda yakin ingin mengirim laporan ini? Anda tidak dapat mengubahnya lagi setelah dikirim.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'submitted',
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil')
                        ->body('Laporan berhasil dikirim untuk review via View Page.')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\MonthlyReportV2\Pages;

use App\Filament\Resources\MonthlyReportV2\MonthlyReportV2Resource;
use App\Models\MonthlyPhoto;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyReportV2 extends EditRecord
{
    protected static string $resource = MonthlyReportV2Resource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->status !== 'draft') {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Laporan dalam status ' . $this->record->status)
                ->body('Anda tidak dapat mengubah laporan yang sudah dikirim atau disetujui.')
                ->persistent()
                ->send();
        }
    }

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
            Actions\DeleteAction::make(),
            Actions\Action::make('submit_for_review')
                ->label('Kirim untuk Review')
                ->color('warning')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan untuk Review')
                ->modalDescription('Apakah Anda yakin ingin mengirim laporan ini? Anda tidak dapat mengubahnya lagi setelah dikirim.')
                ->action(function () {
                    if (empty($this->record->employee_sign)) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal')
                            ->body('Anda harus menandatangani laporan sebelum mengirim.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $this->record->update([
                        'status' => 'submitted',
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil')
                        ->body('Laporan berhasil dikirim untuk review via Edit Page.')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing photos into 'selected_photos'
        $data['selected_photos'] = $this->record->photos()
            ->orderBy('order')
            ->pluck('daily_photo_id')
            ->toArray();

        return $data;
    }

    protected array $selectedPhotos = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedPhotos = $data['selected_photos'] ?? [];
        
        unset($data['selected_photos']);
        unset($data['scope_summary']);

        // Update signed_at if signature changed or is new
        if (!empty($data['employee_sign']) && $data['employee_sign'] !== $this->record->employee_sign) {
             $data['employee_signed_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Sync photos: Delete old ones and create new ones to ensure order is correct
        // Or we could try to update, but deletion/recreation is simpler for ordering.
        $record->photos()->delete();

        foreach ($this->selectedPhotos as $index => $dailyPhotoId) {
            MonthlyPhoto::create([
                'monthly_report_id' => $record->id,
                'daily_photo_id' => $dailyPhotoId,
                'order' => $index + 1,
            ]);
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

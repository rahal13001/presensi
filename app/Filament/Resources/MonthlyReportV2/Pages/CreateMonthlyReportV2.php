<?php

namespace App\Filament\Resources\MonthlyReportV2\Pages;

use App\Filament\Resources\MonthlyReportV2\MonthlyReportV2Resource;
use App\Models\MonthlyPhoto;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMonthlyReportV2 extends CreateRecord
{
    protected static string $resource = MonthlyReportV2Resource::class;

    protected array $selectedPhotos = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedPhotos = $data['selected_photos'] ?? [];
        
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        unset($data['selected_photos']);
        unset($data['scope_summary']);

        if (!empty($data['employee_sign'])) {
            $data['employee_signed_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

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

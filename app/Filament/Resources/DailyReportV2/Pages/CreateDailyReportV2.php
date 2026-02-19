<?php

namespace App\Filament\Resources\DailyReportV2\Pages;

use App\Filament\Resources\DailyReportV2\DailyReportV2Resource;
use App\Models\DailyOtherWork;
use App\Models\DailyPhoto;
use App\Models\DailyScopeCheck;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateDailyReportV2 extends CreateRecord
{
    protected static string $resource = DailyReportV2Resource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure user_id is set for non-admins
        if (! isset($data['user_id'])) {
            $data['user_id'] = Auth::id();
        }

        // Store the virtual fields in temporary properties or just remove them from $data
        // and access them later via $this->data (which persists form state).
        // However, standard CreateRecord passes $data to create(), so we MUST remove them from the returned array.
        
        // We can access the original form data in afterCreate via $this->form->getState() 
        // or by keeping a copy. Ideally, we just remove them here.
        
        // Note: We need to preserve these values to use them in afterCreate.
        // We can create a property on the class to hold them, BUT
        // Filament preserves the form state in $this->data usually? No, $this->data is the array passed to create.
        
        // Let's store them in class properties.
        $this->scopeChecks = $data['scope_checks'] ?? [];
        $this->otherWorks = $data['other_works'] ?? [];
        $this->photos = $data['photos'] ?? [];

        unset($data['scope_checks']);
        unset($data['other_works']);
        unset($data['photos']);

        return $data;
    }

    // Temporary storage for virtual fields
    protected array $scopeChecks = [];
    protected array $otherWorks = [];
    protected array $photos = [];

    protected function afterCreate(): void
    {
        $record = $this->record;
        $user = $record->user; // The user who owns the report

        DB::transaction(function () use ($record, $user) {
            // 1. Handle Scope Checks
            // We need to create a DailyScopeCheck for EVERY scope assigned to the user.
            // Some will be checked, some unchecked.
            
            $assignedScopes = $user->scopes()->where('is_active', true)->get();
            $checkedScopeIds = $this->scopeChecks;

            foreach ($assignedScopes as $scope) {
                DailyScopeCheck::create([
                    'daily_report_id' => $record->id,
                    'scope_id' => $scope->id,
                    'is_checked' => in_array($scope->id, $checkedScopeIds),
                ]);
            }

            // 2. Handle Other Works
            foreach ($this->otherWorks as $otherWorkOptionId) {
                DailyOtherWork::create([
                    'daily_report_id' => $record->id,
                    'other_work_option_id' => $otherWorkOptionId,
                    'description' => null, // Description not in form yet, assuming just checkbox selection
                ]);
            }

            // 3. Handle Photos
            foreach ($this->photos as $photoPath) {
                DailyPhoto::create([
                    'daily_report_id' => $record->id,
                    'photo_path' => $photoPath,
                    'caption' => null, // Optional
                ]);
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

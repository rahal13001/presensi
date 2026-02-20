<?php

namespace App\Filament\Resources\DailyReportV2\Pages;

use App\Filament\Resources\DailyReportV2\DailyReportV2Resource;
use App\Models\DailyOtherWork;
use App\Models\DailyPhoto;
use App\Models\DailyScopeCheck;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditDailyReportV2 extends EditRecord
{
    protected static string $resource = DailyReportV2Resource::class;

    // Temporary storage for virtual fields
    protected array $scopeChecks = [];
    protected array $otherWorks = [];
    protected array $photos = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'admin'])),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        
        // 1. Load Scope Checks (only checked ones)
        // We want the IDs of the scopes that are marked as checked to pre-fill the checkbox list
        $data['scope_checks'] = $record->scopeChecks()
            ->where('is_checked', true)
            ->pluck('scope_id')
            ->toArray();

        // 2. Load Other Works
        $data['other_works'] = $record->otherWorks()
            ->pluck('other_work_option_id')
            ->toArray();

        // 3. Load Photos
        // FileUpload expects an array of paths
        $data['photos'] = $record->photos()
            ->pluck('photo_path')
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Capture virtual fields
        $this->scopeChecks = $data['scope_checks'] ?? [];
        $this->otherWorks = $data['other_works'] ?? [];
        $this->photos = $data['photos'] ?? [];

        // Remove from main update payload
        unset($data['scope_checks']);
        unset($data['other_works']);
        unset($data['photos']);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $user = $record->user;

        DB::transaction(function () use ($record, $user) {
            // 1. Sync Scope Checks
            // Get all assigned scopes for the user
            $assignedScopes = $user->scopes()->where('is_active', true)->get();
            $checkedScopeIds = $this->scopeChecks;

            // Map existing checks by scope_id
            $existingChecks = $record->scopeChecks()->pluck('id', 'scope_id');

            foreach ($assignedScopes as $scope) {
                $isChecked = in_array($scope->id, $checkedScopeIds);

                if (isset($existingChecks[$scope->id])) {
                    // Update existing check
                    DailyScopeCheck::where('id', $existingChecks[$scope->id])->update([
                        'is_checked' => $isChecked,
                    ]);
                } else {
                    // Create new check
                    DailyScopeCheck::create([
                        'daily_report_id' => $record->id,
                        'scope_id' => $scope->id,
                        'is_checked' => $isChecked,
                    ]);
                }
            }
            
            // 2. Sync Other Works
            // Delete existing and recreate
            $record->otherWorks()->delete();
            
            foreach ($this->otherWorks as $otherWorkOptionId) {
                DailyOtherWork::create([
                    'daily_report_id' => $record->id,
                    'other_work_option_id' => $otherWorkOptionId,
                ]);
            }

            // 3. Sync Photos
            // Get existing photo paths from DB
            $existingPhotoPaths = $record->photos()->pluck('photo_path')->toArray();
            
            // Determine photos to delete (in DB but not in form)
            $photosToDelete = array_diff($existingPhotoPaths, $this->photos);
            if (!empty($photosToDelete)) {
                // We delete the DB record. Filament handles the file deletion if configured, 
                // but by default FileUpload handles the storage.
                // We just need to remove the DB rows.
                DailyPhoto::where('daily_report_id', $record->id)
                    ->whereIn('photo_path', $photosToDelete)
                    ->delete();
            }
            
            // Determine photos to add (in form but not in DB)
            $photosToAdd = array_diff($this->photos, $existingPhotoPaths);
            foreach ($photosToAdd as $path) {
                DailyPhoto::create([
                    'daily_report_id' => $record->id,
                    'photo_path' => $path,
                ]);
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

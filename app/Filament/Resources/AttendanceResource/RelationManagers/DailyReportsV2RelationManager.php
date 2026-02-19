<?php

namespace App\Filament\Resources\AttendanceResource\RelationManagers;

use App\Filament\Resources\DailyReportV2\Schemas\DailyReportV2Form;
use App\Models\DailyOtherWork;
use App\Models\DailyPhoto;
use App\Models\DailyReportV2;
use App\Models\DailyScopeCheck;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DailyReportsV2RelationManager extends RelationManager
{
    protected static string $relationship = 'dailyReportsV2';

    public function form(Schema $schema): Schema
    {
        return DailyReportV2Form::configure($schema, isRelationManager: true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('report_date')
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scope_checks_count')
                    ->label('Ruang Lingkup')
                    ->counts('scopeChecks')
                    ->formatStateUsing(fn ($state) => "{$state} item"),
                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Foto')
                    ->counts('photos')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                // No filters needed for relation manager typically as it's scoped to one attendance
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Buat Laporan Baru')
                    ->using(function (array $data, string $model): Model {
                        $attendance = $this->getOwnerRecord();
                        
                        // 1. Capture virtual fields
                        $scopeChecks = $data['scope_checks'] ?? [];
                        $otherWorks = $data['other_works'] ?? [];
                        $photos = $data['photos'] ?? [];

                        unset($data['scope_checks']);
                        unset($data['other_works']);
                        unset($data['photos']);

                        // 2. Set user_id and attendance_id
                        // Inherit from attendance owner
                        $data['user_id'] = $attendance->user_id;
                        // attendance_id is automatically handled by the relationship ->create call?
                        // $relationship->create($data) sets attendance_id automatically.

                        return DB::transaction(function () use ($data, $scopeChecks, $otherWorks, $photos, $attendance) {
                            // Create the main record
                            /** @var DailyReportV2 $record */
                            $record = $this->getRelationship()->create($data);

                            // 3. Handle Relational Data logic (reused from CreateDailyReportV2)
                            
                            // User associated with the report (for scope assignment context)
                            // If we are admin creating for someone else, $attendance->user is the target.
                            $user = $attendance->user; 

                            // Handle Scope Checks
                            $assignedScopes = $user->scopes()->where('is_active', true)->get();
                            
                            foreach ($assignedScopes as $scope) {
                                DailyScopeCheck::create([
                                    'daily_report_id' => $record->id,
                                    'scope_id' => $scope->id,
                                    'is_checked' => in_array($scope->id, $scopeChecks),
                                ]);
                            }

                            // Handle Other Works
                            foreach ($otherWorks as $otherWorkOptionId) {
                                DailyOtherWork::create([
                                    'daily_report_id' => $record->id,
                                    'other_work_option_id' => $otherWorkOptionId,
                                ]);
                            }

                            // Handle Photos
                            foreach ($photos as $photoPath) {
                                DailyPhoto::create([
                                    'daily_report_id' => $record->id,
                                    'photo_path' => $photoPath,
                                ]);
                            }

                            return $record;
                        });
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, DailyReportV2 $record): array {
                        // Load relationships into virtual fields
                        $data['scope_checks'] = $record->scopeChecks()->where('is_checked', true)->pluck('scope_id')->toArray();
                        $data['other_works'] = $record->otherWorks()->pluck('other_work_option_id')->toArray();
                        $data['photos'] = $record->photos()->pluck('photo_path')->toArray();
                        
                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        // Capture virtual fields
                        $scopeChecks = $data['scope_checks'] ?? [];
                        $otherWorks = $data['other_works'] ?? [];
                        $photos = $data['photos'] ?? [];

                        unset($data['scope_checks']);
                        unset($data['other_works']);
                        unset($data['photos']);
                        
                        // user_id and attendance_id should not change on edit
                        
                        return DB::transaction(function () use ($record, $data, $scopeChecks, $otherWorks, $photos) {
                            // Update main record
                            $record->update($data);
                            /** @var DailyReportV2 $record */

                            // Handle Relational Data logic (reused from EditDailyReportV2)
                            $user = $record->user;

                            // Sync Scope Checks
                            $assignedScopes = $user->scopes()->where('is_active', true)->get();
                            $existingChecks = $record->scopeChecks()->pluck('id', 'scope_id');

                            foreach ($assignedScopes as $scope) {
                                $isChecked = in_array($scope->id, $scopeChecks);

                                if (isset($existingChecks[$scope->id])) {
                                    DailyScopeCheck::where('id', $existingChecks[$scope->id])->update(['is_checked' => $isChecked]);
                                } else {
                                    DailyScopeCheck::create([
                                        'daily_report_id' => $record->id,
                                        'scope_id' => $scope->id,
                                        'is_checked' => $isChecked,
                                    ]);
                                }
                            }

                            // Sync Other Works
                            $record->otherWorks()->delete();
                            foreach ($otherWorks as $otherWorkOptionId) {
                                DailyOtherWork::create([
                                    'daily_report_id' => $record->id,
                                    'other_work_option_id' => $otherWorkOptionId,
                                ]);
                            }

                            // Sync Photos
                            $existingPhotoPaths = $record->photos()->pluck('photo_path')->toArray();
                            
                            $photosToDelete = array_diff($existingPhotoPaths, $photos);
                            if (!empty($photosToDelete)) {
                                DailyPhoto::where('daily_report_id', $record->id)->whereIn('photo_path', $photosToDelete)->delete();
                            }
                            
                            $photosToAdd = array_diff($photos, $existingPhotoPaths);
                            foreach ($photosToAdd as $path) {
                                DailyPhoto::create([
                                    'daily_report_id' => $record->id,
                                    'photo_path' => $path,
                                ]);
                            }

                            return $record;
                        });
                    }),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

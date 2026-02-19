<?php

namespace App\Filament\Resources\AttendanceResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use App\Models\Attendance;
use Filament\Tables\Table;
use App\Models\Dailyreport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Noin\FilamentFormsTinyeditor\Components\TinyEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class DailyreportsRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyreports';

    public function form(Schema $schema): Schema
    {
        $attendance = $this->getOwnerRecord();
       
        $isAllowedToFill = false; // Default: Not allowed

        if ($attendance) {
            // Get end date and schedule end time
            $endDate = $attendance->end_date; // Example: "2025-02-19"
            $endSchedule = $attendance->schedule_end_time; // Example: "16:00:00"

            // Combine end_date with (end_schedule - 1 hour)
            $allowedFillTime = date('Y-m-d H:i:s', strtotime("$endDate $endSchedule -1 hour")); 
            
            // Get current time
            $now = date('Y-m-d H:i:s');

            // Allow filling if current time is greater than or equal to allowed fill time
            if (strtotime($now) >= strtotime($allowedFillTime)) {
                $isAllowedToFill = true;
            }
        }

      
        
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->disabled(!$isAllowedToFill)
                    ->columnSpan('full')
                    ->maxLength(255),
                TinyEditor::make('description')
                    ->required()
                    ->label('Deskripsi')
                    ->columnSpan('full')
                    ->disabled(!$isAllowedToFill)
                    ->profile('simple'),
                Forms\Components\Textarea::make('output')
                    ->label('Output')
                    ->required()
                    ->disabled(!$isAllowedToFill)
                    ->columnSpan('full'),
                Forms\Components\Textarea::make('note')
                    ->label('Keterangan')
                    ->disabled(!$isAllowedToFill)
                    ->columnSpan('full'),
                FileUpload::make('dokumentasi1')
                    ->required()
                    ->label('Dokumentasi 1')
                    ->columnSpan('full')
                    ->openable()
                    ->disk('public')
                    ->directory('dokumentasi')
                    ->disabled(!$isAllowedToFill)
                    ->visibility('public')
                    ->maxSize(5000)
                    ->image(),
                FileUpload::make('dokumentasi2')
                    ->label('Dokumentasi 2')
                    ->openable()
                    ->disk('public')
                    ->directory('dokumentasi')
                    ->disabled(!$isAllowedToFill)
                    ->visibility('public')
                    ->maxSize(5000)
                    ->image()
                    ->columnSpan('full'),
                FileUpload::make('documentation3')
                    ->label('Dokumentasi 3')
                    ->openable()
                    ->disk('public')
                    ->directory('dokumentasi_supir')
                    ->disabled(!$isAllowedToFill)
                    ->visibility('public')
                    ->maxSize(5000)
                    ->visible(function(){
                        if (Auth::user()->hasAnyRole(['sopir', 'admin','super_admin'])) {
                            return true;
                        }
                     }    
                    )
                    ->image()
                    ->columnSpan('full'),
                FileUpload::make('documentation4')
                    ->label('Dokumentasi 4')
                    ->openable()
                    ->disk('public')
                    ->visible(function(){
                        if (Auth::user()->hasAnyRole(['sopir', 'admin','super_admin'])) {
                            return true;
                        }
                     }    
                    )
                    ->directory('dokumentasi_supir')
                    ->disabled(!$isAllowedToFill)
                    ->visibility('public')
                    ->maxSize(5000)
                    ->image()
                    ->columnSpan('full'),

                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\Action::make('Lihat')
                    ->url(fn (DailyReport $record) => route('laporanharian', ['dailyreport' => $record->id, 'title' => Str::slug($record->title)])) // ✅ Correct way
                    ->openUrlInNewTab() // Optional: Opens in a new tab
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
        {
            return false;
        }
}

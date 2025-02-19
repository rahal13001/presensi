<?php

namespace App\Filament\Resources\AttendanceResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;

class DailyreportsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;
    protected static string $relationship = 'dailyreports';

    public function form(Form $form): Form
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

      
        
        return $form
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
        {
            return false;
        }
}

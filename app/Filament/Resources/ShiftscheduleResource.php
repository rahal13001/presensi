<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftscheduleResource\Pages;
use App\Filament\Resources\ShiftscheduleResource\RelationManagers;
use App\Models\Shiftschedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftscheduleResource extends Resource
{
    protected static ?string $model = Shiftschedule::class;
    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                           
                            Forms\Components\Select::make('shift_id')
                                ->relationship('shift', 'name')
                                ->required(),
                            Forms\Components\Select::make('office_id')
                                ->relationship('office', 'name')
                                ->required(),
                        ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('Jam Kerja')
                    ->description(fn (Shiftschedule $record): string => $record->shift->start_time.' - '.$record->shift->end_time)
                    ->sortable(),
                 Tables\Columns\TextColumn::make('office.name')
                    ->label('Kantor')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShiftschedules::route('/'),
            // 'create' => Pages\CreateShiftschedule::route('/create'),
            // 'edit' => Pages\EditShiftschedule::route('/{record}/edit'),
        ];
    }
}

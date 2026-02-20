<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftscheduleResource\Pages;
use App\Filament\Resources\ShiftscheduleResource\RelationManagers;
use App\Models\Shiftschedule;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftscheduleResource extends Resource
{
    protected static ?string $model = Shiftschedule::class;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Group::make()
                ->schema([
                    \Filament\Schemas\Components\Section::make()
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
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'admin'])),
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

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Jadwal Shift";
        }
        else
        {
            return "Shift Schedule";
        }
    }

    public static function getNavigationGroup(): string | null
    {
        return 'Attendance Management';
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-o-clock';
    }
}

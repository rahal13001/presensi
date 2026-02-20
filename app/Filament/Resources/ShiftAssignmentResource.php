<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Models\ShiftAssignment;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ShiftAssignmentResource\Pages;
use App\Filament\Resources\ShiftAssignmentResource\RelationManagers;

class ShiftAssignmentResource extends Resource
{
    protected static ?string $model = ShiftAssignment::class;



    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->name('Pengguna')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('shift_id')
                    ->relationship('shift', 'name')
                    ->name('Shift')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')->required()
                    ->name('Tanggal'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable()
                    ->label('User'),
                TextColumn::make('shift.name')->label('Shift')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Assignment Date')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
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
            'index' => Pages\ListShiftAssignments::route('/'),
            'create' => Pages\CreateShiftAssignment::route('/create'),
            'view' => Pages\ViewShiftAssignment::route('/{record}'),
            'edit' => Pages\EditShiftAssignment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): string | null
    {
        return 'Office Management';
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-s-identification';
    }
}

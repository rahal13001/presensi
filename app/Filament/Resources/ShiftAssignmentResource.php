<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-s-identification';

    protected static ?string $navigationGroup = 'Office Management';

    public static function form(Form $form): Form
    {
        return $form
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListShiftAssignments::route('/'),
            'create' => Pages\CreateShiftAssignment::route('/create'),
            'view' => Pages\ViewShiftAssignment::route('/{record}'),
            'edit' => Pages\EditShiftAssignment::route('/{record}/edit'),
        ];
    }
}

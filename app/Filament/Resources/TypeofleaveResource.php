<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeofleaveResource\Pages;
use App\Filament\Resources\TypeofleaveResource\RelationManagers;
use App\Models\Typeofleave;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeofleaveResource extends Resource
{
    protected static ?string $model = Typeofleave::class;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('leaves_name')
                    ->label('Jenis Cuti')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaves_name')
                    ->label('Jenis Cuti')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListTypeofleaves::route('/'),
            // 'create' => Pages\CreateTypeofleave::route('/create'),
            // 'edit' => Pages\EditTypeofleave::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Jenis Cuti";
        }
        else
        {
            return "Type of Leaves";
        }
    }

    public static function getNavigationGroup(): string | null
    {
        return 'Office Management';
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-o-receipt-refund';
    }
}

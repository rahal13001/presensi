<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        \Filament\Schemas\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('team_name')
                                    ->label('Nama Tim')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('user_id')
                                    ->label('Ketua Tim')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                        ]),
                \Filament\Schemas\Components\Group::make()
                        ->schema([
                                \Filament\Schemas\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('team_number')
                                        ->label('Nomor Tim Kerja')
                                        ->maxLength(255),
                                ])
                            ]),
                        
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team_number')
                    ->label('Nomor Tim Kerja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_name')
                    ->label('Nama Tim Kerja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Ketua Tim Kerja')
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
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTeams::route('/'),
            // 'create' => Pages\CreateTeam::route('/create'),
            // 'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Tim Kerja";
        }
        else
        {
            return "Team";
        }
    }

    public static function getNavigationGroup(): string | null
    {
        return 'User Management';
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-o-user-group';
    }
}

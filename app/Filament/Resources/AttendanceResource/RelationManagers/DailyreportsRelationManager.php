<?php

namespace App\Filament\Resources\AttendanceResource\RelationManagers;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;

class DailyreportsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;
    protected static string $relationship = 'dailyreports';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->columnSpan('full')
                    ->maxLength(255),
                TinyEditor::make('description')
                    ->required()
                    ->label('Deskripsi')
                    ->columnSpan('full')
                    ->profile('simple'),
                Forms\Components\Textarea::make('output')
                    ->label('Output')
                    ->required()
                    ->columnSpan('full'),
                Forms\Components\Textarea::make('note')
                    ->label('Keterangan')
                    ->columnSpan('full'),
                FileUpload::make('dokumentasi1')
                    ->required()
                    ->label('Dokumentasi 1')
                    ->columnSpan('full')
                    ->openable()
                    ->disk('public')
                    ->directory('dokumentasi')
                    ->visibility('public')
                    ->maxSize(5000)
                    ->image(),
                FileUpload::make('dokumentasi2')
                    ->label('Dokumentasi 2')
                    ->openable()
                    ->disk('public')
                    ->directory('dokumentasi')
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

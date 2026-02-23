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
                \Filament\Schemas\Components\Section::make('Detail Jenis Cuti')
                    ->schema([
                        Forms\Components\TextInput::make('leaves_name')
                            ->label('Jenis Cuti')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan / Aturan Pustaka')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                    
                \Filament\Schemas\Components\Section::make('Pengaturan Cuti')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('has_quota')
                            ->label('Punya Batas Kuota Tahunan')
                            ->live(), // Add live() so it reacts immediately
                            
                        Forms\Components\TextInput::make('default_quota_days')
                            ->label('Kuota Default (Hari)')
                            ->numeric()
                            ->required(fn ($get) => $get('has_quota') === true)
                            ->visible(fn ($get) => $get('has_quota') === true)
                            ->minValue(1),
                            
                        Forms\Components\Toggle::make('requires_attachment')
                            ->label('Wajib Lampirkan Dokumen Pendukung')
                            ->columnSpanFull()
                            ->helperText('Contoh: Surat sakit dari dokter.'),
                            
                        Forms\Components\Toggle::make('is_working_days_only')
                            ->label('Hanya Hitung Hari Kerja')
                            ->default(true)
                            ->columnSpanFull()
                            ->helperText('Jika aktif, libur nasional dan akhir pekan tidak dihitung memotong cuti (misal: Cuti Tahunan). Jika tidak, memotong sesuai kalender (misal: Sakit).'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaves_name')
                    ->label('Jenis Cuti')
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('has_quota')
                    ->label('Batas Kuota')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('default_quota_days')
                    ->label('Hari Kuota')
                    ->numeric()
                    ->sortable()
                    ->placeholder('-'),
                    
                Tables\Columns\IconColumn::make('requires_attachment')
                    ->label('Wajib Dokumen')
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('is_working_days_only')
                    ->label('Hanya Hari Kerja')
                    ->boolean(),
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

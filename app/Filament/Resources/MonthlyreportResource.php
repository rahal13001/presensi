<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Team;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Monthlyreport;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\FormsComponent;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MonthlyreportResource\Pages;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\MonthlyreportResource\RelationManagers;

class MonthlyreportResource extends Resource
{
    protected static ?string $model = Monthlyreport::class;

    
    protected static ?string $navigationGroup = 'Monthly Report';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->label('Pegawai')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default(fn () => auth()->user()->hasRole('user') ? auth()->id() : null) // Auto-select if role is "user"
                                ->disabled(fn () => auth()->user()->hasRole('user')), // Disable selection if user is "user"
                            Forms\Components\Select::make('team_name')
                                ->options(
                                    Team::select('team_name')
                                    ->distinct()  // Ensure no duplicate team names
                                    ->pluck('team_name', 'team_name')
                                )
                                ->label('Tim Kerja')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    // Find the team by name and get its leader's name
                                    $team = Team::where('team_name', $state)->with('user')->first();
                                    $leaderName = $team?->user?->name ?? 'Tidak Ada Ketua'; // Assuming 'leader' is a relation to the User model
                                    $set('team_leader', $leaderName); // Automatically set the team_leader field

                                    $leaderIdNumber = $team?->user?->idnumber ?? 'Tidak Ada NIP';
                                    $set('team_idnumber', $leaderIdNumber);
                                }),
                           
                        ])
                    ]),

                    Forms\Components\Group::make()
                        ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('month')
                                ->label('Bulan')
                                ->options([
                                    '1' => 'Januari',
                                    '2' => 'Februari',
                                    '3' => 'Maret',
                                    '4' => 'April',
                                    '5' => 'Mei',
                                    '6' => 'Juni',
                                    '7' => 'Juli',
                                    '8' => 'Agustus',
                                    '9' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('year')
                                ->label('Tahun')
                                ->numeric()
                                ->maxLength(4)
                                ->required(),
                            
                            
                            ])
                        ]),
                    
                        Forms\Components\Group::make()
                        ->schema([
                           
                            Forms\Components\Section::make()
                                ->description('Tanda Tangan Pegawai')
                                ->schema([
                                    Forms\Components\DatePicker::make('sign_date')
                                    ->label('Tanggal Tanda Tangan')
                                    ->required(),
                                    SignaturePad::make('user_sign')
                                        ->label('Tanda Tangan Pegawai')
                                        ->dotSize(2.0)
                                        ->lineMinWidth(0.5)
                                        ->lineMaxWidth(2.5)
                                        ->throttle(16)
                                        ->minDistance(5)
                                        ->exportPenColor('#000')
                                        ->velocityFilterWeight(0.7),
                                ])
                            
                        ]),

                        Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Section::make()
                            ->description('Tanda Tangan Ketua Tim Kerja')
                            ->schema([


                                Forms\Components\TextInput::make('team_leader')
                                    ->label('Nama Ketua Tim Kerja')
                                    ->maxLength(255),
                                 
                                Forms\Components\TextInput::make('team_idnumber')
                                    ->label('NIP Ketua Tim Kerja')
                                    ->maxLength(255),

                                SignaturePad::make('team_sign')
                                    ->label('Tanda Tangan Katimja')
                                    ->dotSize(2.0)
                                    ->lineMinWidth(0.5)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                                    ->exportPenColor('#000')
                                    ->hidden(function ($record) {
                                        $user = auth()->user();
                                        return !$user->hasRole(['super_admin', 'kepala']); // Hide if the user is not 'super_admin' or 'kepala'
                                    }),

                                
                            ])
                            
                        ]),

                        Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Section::make()
                                ->description('Tanda Tangan Katimja Dukungan Manajemen')
                                ->schema([
                                    Forms\Components\TextInput::make('dukman_leader')
                                        ->label('Nama Katimja Dukungan Manajemen')
                                        ->maxLength(255)
                                        ->default('Hendrik Sombo, S.Pi., M.Si.'),
                                    Forms\Components\TextInput::make('dukman_idnumber')
                                        ->label('NIP Katimja Dukungan Manajemen')
                                        ->maxLength(255)
                                        ->default('198201312005021001'),
                                    SignaturePad::make('dukman_sign')
                                        ->label('Tanda Tangan Katimja Dukmungan Manajemen')
                                        ->dotSize(2.0)
                                        ->lineMinWidth(0.5)
                                        ->lineMaxWidth(2.5)
                                        ->throttle(16)
                                        ->minDistance(5)
                                        ->exportPenColor('#000')
                                        ->velocityFilterWeight(0.7),
                                ])
                                ->hidden(function ($record) {
                                    $user = auth()->user();
                                        return !$user->hasRole(['super_admin', 'kepala']);
                                })
                            
                        ]),
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                
                $is_user = Auth::user()->hasRole('user');
                

                if ($is_user) {
                    $query->where('monthlyreports.user_id', Auth::user()->id)
                          ->orderBy('year', 'desc')
                          ->orderBy('month', 'desc');
                } else {
                    $query->orderBy('year', 'desc')
                          ->orderBy('month', 'desc');
                }
                
            })
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Pegawai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('team_name')
                    ->searchable()
                    ->label('Tim Kerja')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->searchable()
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => [
                        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                        '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                        '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ][$state] ?? 'Unknown'),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('team_sign')
                    ->label('TTD Katimja')
                    ->getStateUsing(fn ($record) => !is_null($record->team_sign)) // Convert null to false
                    ->trueIcon('heroicon-o-check-circle')   // Green check icon
                    ->falseIcon('heroicon-o-x-circle')      // Red cross icon
                    ->trueColor('success')                  // Green color for true
                    ->falseColor('danger'),
                
                Tables\Columns\BooleanColumn::make('dukman_sign')
                    ->label('TTD Dukman')
                    ->getStateUsing(fn ($record) => !is_null($record->dukman_sign)) // Convert null to false
                    ->trueIcon('heroicon-o-check-circle')   // Green check icon
                    ->falseIcon('heroicon-o-x-circle')      // Red cross icon
                    ->trueColor('success')                  // Green color for true
                    ->falseColor('danger'),

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

                SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(array_combine(
                        range(Carbon::now()->year - 5, Carbon::now()->year),
                        range(Carbon::now()->year - 5, Carbon::now()->year)
                    ))
                    ->default(Carbon::now()->year),

                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret',
                        '4' => 'April', '5' => 'Mei', '6' => 'Juni',
                        '7' => 'Juli', '8' => 'Augustus', '9' => 'September',
                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ]),
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

    public static function infolist(Infolist $infolist): Infolist
        {
            return $infolist
                ->schema([
                    Section::make('Informasi Penyusun')
                        ->schema([
                            TextEntry::make('user.name')
                                ->label('Nama')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('position.position_name')
                                ->label('Jabatan')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('team_name')
                                ->label('Tim Kerja')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('team_leader')
                                ->label('Ketua Tim Kerja')
                                ->weight(FontWeight::Bold),
                        ])->columns(2)
                        ->collapsible(),
                    Section::make('Tahun dan Bulan Laporan')
                        ->schema([
                            TextEntry::make('year')
                                ->label('Tahun')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('month')
                                ->label('Bulan')
                                ->weight(FontWeight::Bold)
                                ->formatStateUsing(fn ($state) => [
                                    '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                                    '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                                    '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ][$state] ?? 'Unknown'),
                            ])->columns(2)
                        ->collapsible(),
                    Section::make('Pengesahan')
                        ->schema([
                            ImageEntry::make('team_sign')
                                ->height(150)
                                ->label('Tanda Tangan Katimja'),
                            ImageEntry::make('user_sign')
                                ->height(150)
                                ->label('Tanda Tangan Penyusun'),
                            
                        ])->columns(2)
                    ->collapsible(),
                    Section::make('Pengesahan Dukungan Manajemen')
                        ->schema([
                            TextEntry::make('dukman_leader')
                                ->label('Nama')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('dukman_idnumber')
                                ->label('NIP')
                                ->weight(FontWeight::Bold),
                            ImageEntry::make('dukman_sign')
                                ->height(150)
                                ->label('Tanda Tangan Katimja'),
                        ])->columns(2)
                    ->collapsible()
                    ->hidden(fn ($record) => empty($record->dukman_sign)),
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
            'index' => Pages\ListMonthlyreports::route('/'),
            'create' => Pages\CreateMonthlyreport::route('/create'),
            'view' => Pages\ViewMonthlyreport::route('/{record}'),
            'edit' => Pages\EditMonthlyreport::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Laporan Bulanan";
        }
        else
        {
            return "Monthly Report";
        }
    }
}

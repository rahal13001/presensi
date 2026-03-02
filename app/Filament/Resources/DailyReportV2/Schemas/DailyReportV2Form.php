<?php

namespace App\Filament\Resources\DailyReportV2\Schemas;

use App\Models\OtherWorkOption;
use App\Models\Scope;
use Filament\Forms\Get;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyReportV2Form
{
    public static function configure(Schema $schema, bool $isRelationManager = false): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Info Laporan')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('report_date_display')
                                    ->label('Tanggal Laporan')
                                    ->formatStateUsing(function ($state, $livewire, $record) use ($isRelationManager) {
                                        if ($isRelationManager) {
                                            $date = $record ? $record->report_date : ($livewire->getOwnerRecord()->start_date ?? now());
                                            return \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');
                                        }
                                        return $state;
                                    })
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible($isRelationManager),

                                DatePicker::make('report_date')
                                    ->label('Tanggal Laporan')
                                    ->displayFormat('l, d F Y')
                                    ->native(false)
                                    ->required()
                                    ->default(function ($livewire) use ($isRelationManager) {
                                        if ($isRelationManager && method_exists($livewire, 'getOwnerRecord')) {
                                            return $livewire->getOwnerRecord()->start_date ?? now();
                                        }
                                        return now();
                                    })
                                    ->maxDate(now())
                                    ->visible(! $isRelationManager)
                                    ->dehydrated()
                                    // Unique per user per day validation is handled in database and backend rules,
                                    // but we can add a custom rule here if needed, or rely on model constraints.
                                    ->rule(function ($get) {
                                        return Rule::unique('daily_reports_v2', 'report_date')
                                            ->where('user_id', $get('user_id') ?? Auth::id())
                                            ->ignore($get('id')); // Ignore current record on edit
                                    }),
    
                                Select::make('attendance_id')
                                    ->label('Kehadiran Terkait')
                                    ->relationship(
                                        name: 'attendance',
                                        modifyQueryUsing: function ($query) {
                                            return $query->where('user_id', Auth::id())->orderBy('start_date', 'desc');
                                        }
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->start_date} - " . ($record->start_time ?? 'Belum Absen'))
                                    ->nullable()
                                    ->searchable()
                                    ->hidden($isRelationManager) // Hidden if used in Relation Manager
                                    ->preload(),
                                
                                // User ID is automatically handled for regular users, but admins might want to see it?
                                // Prompt says: "user_id — Hidden field, auto-set to Auth::id() for employees. Select for admin users."
                                Select::make('user_id')
                                    ->label('Pegawai')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(fn () => Auth::id())
                                    ->live() // Make it reactive for scope population
                                    ->visible(fn () => ! $isRelationManager && (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('admin'))),
                                
                                Hidden::make('user_id')
                                    ->default(fn () => Auth::id())
                                    ->visible(fn () => ! $isRelationManager && ! (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('admin'))),
                            ])->columns(2),

                        Section::make('Ruang Lingkup (Scope)')
                            ->description('Centang ruang lingkup yang Anda kerjakan hari ini.')
                            ->schema([
                                CheckboxList::make('scope_checks')
                                    ->label('Daftar Ruang Lingkup')
                                    ->options(function ($get, $livewire) use ($isRelationManager) {
                                        $userId = null; 

                                        // 1. Check if we are in a Relation Manager context for an Attendance record
                                        if ($isRelationManager && method_exists($livewire, 'getOwnerRecord')) {
                                            $userId = $livewire->getOwnerRecord()->user_id;

                                        // 2. Fallback to form state (e.g. standard resource Create/Edit page)
                                        } else {
                                            $userId = $get('user_id');
                                        }

                                        // 3. Default to current user if no user selected yet (e.g. creating initially)
                                        if (! $userId) {
                                            $userId = Auth::id();
                                        }

                                        if (! $userId) {
                                            return [];
                                        }
                                        
                                        // Use FQCN for User model just in case it's not imported
                                        return \App\Models\User::find($userId)?->scopes()
                                            ->where('scopes.is_active', true) 
                                            ->orderBy('order')
                                            ->pluck('name', 'scopes.id') ?? [];
                                    })
                                    ->columns(1)
                                    ->bulkToggleable()
                                    ->required(), // At least one scope? Prompt didn't strictly say required, but usually implies activity.
                            ]),

                        Section::make('Pekerjaan Lain (Other Work)')
                            ->schema([
                                CheckboxList::make('other_works')
                                    ->label('Pekerjaan Lain')
                                    ->options(OtherWorkOption::where('is_active', true)->pluck('name', 'id'))
                                    ->columns(2)
                                    ->bulkToggleable(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Dokumentasi')
                            ->schema([
                                FileUpload::make('photos')
                                    ->label('Foto Kegiatan (Min 1, Max 3)')
                                    ->multiple()
                                    ->minFiles(1)
                                    ->maxFiles(3)
                                    ->image()
                                    ->maxSize(6144) // 6MB
                                    ->directory('daily-photos')
                                    ->required(),
                            ]),

                        Section::make('Catatan')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Catatan Tambahan')
                                    ->rows(3),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}

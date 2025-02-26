<?php

namespace App\Filament\Resources;

use Auth;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Schedule;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Exports\AttendanceExport;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;

use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use App\Filament\Resources\AttendanceResource\RelationManagers\DailyreportsRelationManager;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?string $slug = 'kehadiran';

    protected static ?int $navigationSort = 7;
    
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
                                ->required()
                                ->live() // Make it reactive
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $schedule = Schedule::where('user_id', $state)->with(['office', 'shift'])->first();
                                
                                        // If a schedule exists, set the values from the database
                                        $set('schedule_latitude', optional($schedule?->office)->latitude ?? -0.8908446);
                                        $set('schedule_longitude', optional($schedule?->office)->longitude ?? 131.3208711);
                                
                                        $set('schedule_start_time', optional($schedule?->shift)->start_time ?? null);
                                        $set('schedule_end_time', optional($schedule?->shift)->end_time ?? null);
                                    } else {
                                        // If no user is selected, use fallback defaults
                                        $set('schedule_latitude', null);
                                        $set('schedule_longitude', null);
                                        $set('schedule_start_time', null);
                                        $set('schedule_end_time', null);
                                    }
                                }),

                            Forms\Components\DatePicker::make('start_date')
                                ->label('Tanggal Masuk')
                                ->required(),
                            Forms\Components\DatePicker::make('end_date')
                                ->required()
                                ->label('Tanggal Pulang'),
                            Forms\Components\Toggle::make('not_present')
                                ->label('Tidak Hadir'),
                            Forms\Components\Toggle::make('is_leave')
                                ->label('Cuti'),
                        ])
                ]),

                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('schedule_latitude')
                                ->required()
                                ->numeric(),
                            
                            Forms\Components\TextInput::make('schedule_longitude')
                                ->required()
                                ->numeric(),

                            Forms\Components\TimePicker::make('schedule_start_time')
                                ->label('Jadwal Jam Masuk')
                                ->required(),
                            
                            Forms\Components\TextInput::make('schedule_end_time')
                                ->label('Jadwal Jam Pulang')
                                ->required(),
            
                        ])
                ]),
                    
                
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('start_latitude')
                                ->label('Latitude Masuk')
                                ->numeric(),
                            Forms\Components\TextInput::make('start_longitude')
                                ->label('Longitude Masuk')
                                ->numeric(),
                            Forms\Components\TextInput::make('start_time')
                                ->label('Jam Masuk'),
                        ])
                ]),
                
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('end_latitude')
                                ->label('Latitude Pulang')
                                ->numeric(),
                            Forms\Components\TextInput::make('end_longitude')
                                ->label('Longitude Pulang')
                                ->numeric(),
                            Forms\Components\TextInput::make('end_time')
                                ->label('Jam Pulang'),
                        ])
                ]),

               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $is_user = Auth::user()->hasRole('user');

                if ($is_user) {
                    $query->where('attendances.user_id', Auth::user()->id);
                }
                
            })
            ->paginated([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_late')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (is_null($record->start_time) && is_null($record->end_time)) {
                            return 'Tidak Masuk';
                        }
                        
                        return $record->lateDuration() === "0 jam 0 menit" ? 'Tepat Waktu' : 'Terlambat';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'danger',
                        'Tidak Masuk' => 'warning',
                    })
                    ->description(fn (Attendance $record): string => 'Durasi Terlambat: ' . $record->lateDuration()),
                Tables\Columns\TextColumn::make('work_duration')
                    ->label('Durasi Kerja')
                    ->getStateUsing(fn ($record) => $record->workDuration()),
               
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Datang'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Waktu Pulang'),
                
                Tables\Columns\BooleanColumn::make('is_report')
                    ->label('Laporan')
                    ->trueIcon('heroicon-o-check-circle')   // Green check icon for true
                    ->falseIcon('heroicon-o-x-circle')      // Red cross icon for false
                    ->trueColor('success')                  // Green color for true
                    ->falseColor('danger'),          // Red color for false
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->defaultSort('start_date', 'desc')
            ->groups([
                Group::make('user.name')
                    ->label('Pegawai')
                    ->collapsible(),
                Group::make('start_date')
                    ->label('Tanggal')
                    ->collapsible(),
                
            ])
            ->defaultGroup('start_date')
           
            ->filters([

                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Pegawai'),

                Filter::make('start_date')
                    ->label('Tanggal')
                    ->form(
                        [
                            DatePicker::make('created_from')
                                ->label('Dari'),
                            DatePicker::make('created_until')
                                ->label('Sampai')
                        ]
                    )->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                function($query) use ($data) {
                                    return $query->whereDate('start_date', '>=', $data['created_from']);
                                }
                            )
                            ->when(
                                $data['created_until'],
                                function($query) use ($data) {
                                    return $query->whereDate('start_date', '<=', $data['created_until']);
                                }
                            );
                    })->indicator('start_date'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                RelationManagerAction::make('dailyreports-relation-manager')
                    ->label('Laporan')
                    ->relationManager(DailyreportsRelationManager::make()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    BulkAction::make('export')
                    ->label('Export Selected')
                    ->action(function ($records) {
                        // Convert the collection to an array of IDs
                        $selectedIds = $records->pluck('id')->toArray();
                
                        // Pass the IDs to the export class
                        $export = new AttendanceExport($selectedIds);
                
                        // Download the export file
                        return \Maatwebsite\Excel\Facades\Excel::download($export, 'attendance-' . date('Y-m-d') . '.xlsx');
                    }),
                    
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DailyreportsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Kehadiran";
        }
        else
        {
            return "Attendance";
        }
    }
}

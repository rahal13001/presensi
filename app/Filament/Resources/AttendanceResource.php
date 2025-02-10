<?php

namespace App\Filament\Resources;

use Auth;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use App\Filament\Resources\AttendanceResource\RelationManagers\DailyreportsRelationManager;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Date;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?int $navigationSort = 7;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('schedule_latitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('schedule_longitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('schedule_start_time')
                    ->required(),
                Forms\Components\TextInput::make('schedule_end_time')
                    ->required(),
                Forms\Components\TextInput::make('start_latitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('start_longitude')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Tanggal Masuk')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('updated_at')
                    ->label('Tanggal Pulang')
                    ->required(),
                Forms\Components\TextInput::make('end_time')
                    ->required(),
                Forms\Components\TextInput::make('end_latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('end_longitude')
                    ->numeric(),
                Forms\Components\Toggle::make('not_present')
                    ->label('Tidak Hadir'),
                Forms\Components\Toggle::make('is_leave')
                    ->label('Cuti'),
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
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
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
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
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
                                    return $query->whereDate('created_at', '>=', $data['created_from']);
                                }
                            )
                            ->when(
                                $data['created_until'],
                                function($query) use ($data) {
                                    return $query->whereDate('created_at', '<=', $data['created_until']);
                                }
                            );
                    })->indicator('created_at'),
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
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Auth;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?int $navigationSort = 6;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        \Filament\Schemas\Components\Section::make()
                            ->schema([
                                Forms\Components\Toggle::make('is_banned'),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->unique(ignoreRecord: true)
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('shift_id')
                                    ->relationship('shift', 'name')
                                    ->required(),
                                Forms\Components\Toggle::make('is_wfa'),

                                Forms\Components\Select::make('office_id')
                                    ->relationship('office', 'name')
                                    ->required(),

                            ])
                ]),
                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        \Filament\Schemas\Components\Section::make()
                            ->schema([
                                Forms\Components\Repeater::make('wfaday')
                                ->label('Hari WFA')
                                ->relationship('wfaday') // ✅ Use the relationship correctly
                                ->schema([
                                    Forms\Components\Select::make('day_name')
                                        ->label('Hari')
                                        ->options([
                                            'Monday' => 'Senin',
                                            'Tuesday' => 'Selasa',
                                            'Wednesday' => 'Rabu',
                                            'Thursday' => 'Kamis',
                                            'Friday' => 'Jumat',
                                            'Saturday' => 'Sabtu',
                                            'Sunday' => 'Minggu',
                                        ])
                                        ->required(),
                                ])

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
                    $query->where('user_id', Auth::user()->id);
                }
            })

            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_banned')
                    ->hidden(fn () => !Auth::user()->hasRole('super_admin')),
                Tables\Columns\BooleanColumn::make('is_wfa')
                    ->label('WFA'),
                Tables\Columns\TextColumn::make('shift.name')
                    ->description(fn (Schedule $record): string => $record->shift->start_time.' - '.$record->shift->end_time)
                    ->sortable(),
                Tables\Columns\TextColumn::make('office.name')
                    ->sortable(),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Jadwal";
        }
        else
        {
            return "Schedule";
        }
    }

    public static function getNavigationGroup(): string | null
    {
        return 'Attendance Management';
    }

    public static function getNavigationIcon(): string | null
    {
        return 'heroicon-m-calendar-days';
    }
}

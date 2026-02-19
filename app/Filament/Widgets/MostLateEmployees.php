<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Attendance;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;

class MostLateEmployees extends BaseWidget
{
    protected static ?string $heading = 'Top 5 Pegawai Telat Bulan Ini';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\User::query()
                    ->join('attendances', 'users.id', '=', 'attendances.user_id')
                    ->select('users.*', DB::raw('SUM(
                        CASE 
                            WHEN attendances.start_time IS NULL AND attendances.end_time IS NULL THEN 0
                            WHEN attendances.start_time IS NULL THEN 240
                            WHEN TIME(attendances.start_time) <= TIME(attendances.schedule_start_time) THEN 0
                            ELSE TIMESTAMPDIFF(MINUTE, attendances.schedule_start_time, attendances.start_time)
                        END
                    ) as total_late_minutes'))
                    ->whereNotNull('attendances.schedule_start_time')
                    ->groupBy('users.id')
            )
            ->filters([
                // Month Filter
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        '1' => 'January', '2' => 'February', '3' => 'March',
                        '4' => 'April', '5' => 'May', '6' => 'June',
                        '7' => 'July', '8' => 'August', '9' => 'September',
                        '10' => 'October', '11' => 'November', '12' => 'December'
                    ])
                    ->default(Carbon::now()->month)
                    ->query(fn ($query, $state) => $query->whereMonth('attendances.start_date', $state)),

                // Year Filter
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(array_combine(
                        range(Carbon::now()->year - 5, Carbon::now()->year),
                        range(Carbon::now()->year - 5, Carbon::now()->year)
                    ))
                    ->default(Carbon::now()->year)
                    ->query(fn ($query, $state) => $query->whereYear('attendances.start_date', $state)),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('total_late_minutes')
                    ->label('Total Terlambat')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => floor($state / 60) . ' Jam ' . ($state % 60) . ' Menit'),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('total_late_minutes', 'desc');
    }
}

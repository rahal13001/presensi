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
            Attendance::select('user_id', DB::raw('SUM(
                CASE 
                    WHEN start_time IS NULL AND end_time IS NULL THEN 0  -- No lateness if both are null
                    WHEN start_time IS NULL THEN 240  -- 4-hour penalty for missing start_time
                    WHEN TIME(start_time) <= TIME(schedule_start_time) THEN 0  -- Not late
                    ELSE TIMESTAMPDIFF(MINUTE, schedule_start_time, start_time)
                END
            ) as total_late_minutes'))
            ->whereNotNull('schedule_start_time')
            ->groupBy('user_id')
            ->orderByDesc('total_late_minutes')
            ->addSelect('user_id as id')  // Ensure each record has a unique ID
            ->with('user')
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
                ->query(fn ($query, $state) => $query->whereMonth('created_at', $state)),

            // Year Filter
            SelectFilter::make('year')
                ->label('Year')
                ->options(array_combine(
                    range(Carbon::now()->year - 5, Carbon::now()->year),
                    range(Carbon::now()->year - 5, Carbon::now()->year)
                ))
                ->default(Carbon::now()->year)
                ->query(fn ($query, $state) => $query->whereYear('created_at', $state)),
        ])
        ->columns([
            Tables\Columns\TextColumn::make('user.name')
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

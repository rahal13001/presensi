<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Attendance;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class LongestWorkTimeEmployees extends BaseWidget
{
    
    protected static ?string $heading = 'Top 5 Pegawai Jam Kerja Terpanjang';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::select('user_id', DB::raw('SUM(
                    CASE 
                        WHEN start_time IS NULL AND end_time IS NULL THEN 0  -- 0 minutes if both times are null
                        WHEN end_time IS NULL THEN 240  -- Cap at 4 hours (240 minutes) if end_time is missing
                        WHEN start_time IS NULL THEN TIMESTAMPDIFF(MINUTE, ADDTIME(schedule_start_time, "04:00:00"), end_time)  -- 4-hour penalty for missing start_time
                        WHEN TIME(end_time) < TIME(start_time) THEN TIMESTAMPDIFF(MINUTE, start_time, ADDTIME(end_time, "24:00:00"))  -- Handle overnight shift
                        ELSE TIMESTAMPDIFF(MINUTE, start_time, end_time)  -- Normal duration calculation
                    END
                ) as total_work_minutes'))
                ->whereNotNull('schedule_start_time')
                ->groupBy('user_id')
                ->orderByDesc('total_work_minutes')
                ->addSelect('user_id as id')
                ->with('user')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
    
                Tables\Columns\TextColumn::make('total_work_minutes')
                    ->label('Total Durasi Kerja')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => floor($state / 60) . ' Jam ' . ($state % 60) . ' Menit'),
            ])
            ->filters([
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
    
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(array_combine(
                        range(Carbon::now()->year - 5, Carbon::now()->year),
                        range(Carbon::now()->year - 5, Carbon::now()->year)
                    ))
                    ->default(Carbon::now()->year)
                    ->query(fn ($query, $state) => $query->whereYear('created_at', $state)),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('total_work_minutes', 'desc');
    }
}

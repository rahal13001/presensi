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
                \App\Models\User::query()
                    ->join('attendances', 'users.id', '=', 'attendances.user_id')
                    ->select('users.id', 'users.name', DB::raw('SUM(
                        CASE 
                            WHEN attendances.start_time IS NULL AND attendances.end_time IS NULL THEN 0
                            WHEN attendances.end_time IS NULL THEN 240
                            WHEN attendances.start_time IS NULL THEN TIMESTAMPDIFF(MINUTE, ADDTIME(attendances.schedule_start_time, "04:00:00"), attendances.end_time)
                            WHEN TIME(attendances.end_time) < TIME(attendances.start_time) THEN TIMESTAMPDIFF(MINUTE, attendances.start_time, ADDTIME(attendances.end_time, "24:00:00"))
                            ELSE TIMESTAMPDIFF(MINUTE, attendances.start_time, attendances.end_time)
                        END
                    ) as total_work_minutes'))
                    ->whereNotNull('attendances.schedule_start_time')
                    ->groupBy('users.id', 'users.name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
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
                    ->query(fn ($query, $state) => $query->whereMonth('attendances.start_date', $state)),
    
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(array_combine(
                        range(Carbon::now()->year - 5, Carbon::now()->year),
                        range(Carbon::now()->year - 5, Carbon::now()->year)
                    ))
                    ->default(Carbon::now()->year)
                    ->query(fn ($query, $state) => $query->whereYear('attendances.start_date', $state)),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('total_work_minutes', 'desc');
    }
}

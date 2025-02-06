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
                Attendance::select('user_id', DB::raw('SUM(TIMESTAMPDIFF(MINUTE, schedule_start_time, start_time)) as total_late_minutes'))
                ->whereNotNull('start_time') // Ensure valid attendance records
                ->whereColumn('start_time', '>', 'schedule_start_time') // Late condition
                ->groupBy('user_id')
                ->orderByDesc('total_late_minutes')
                ->limit(5)
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
                ->label('Total')
                ->sortable()
                ->numeric()
                ->formatStateUsing(fn ($state) => floor($state / 60) . ' Jam ' . ($state % 60) . ' Menit'),
        ])
        ->modifyQueryUsing(fn ($query) => $query->addSelect(DB::raw('user_id as id')))
        ->defaultSort('total_late_minutes', 'desc');
    }
}

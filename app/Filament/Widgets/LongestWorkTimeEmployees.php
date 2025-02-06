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
                Attendance::select('user_id', DB::raw('SUM(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as total_work_minutes'))
                ->whereNotNull('end_time') // Only count completed shifts
                ->groupBy('user_id')
                ->orderByDesc('total_work_minutes')
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
                ->label('Employee Name')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('total_work_minutes')
                ->label('Total Work Time')
                ->sortable()
                ->numeric()
                ->formatStateUsing(fn ($state) => floor($state / 60) . ' Jam ' . ($state % 60) . ' Menit'),
        ])
        ->modifyQueryUsing(fn ($query) => $query->addSelect(DB::raw('user_id as id')))
        ->defaultSort('total_work_minutes', 'desc');
    }
}

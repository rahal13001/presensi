<?php

namespace App\Filament\Resources\LeaveResource\Widgets;

use App\Models\Typeofleave;
use App\Models\LeaveQuota;
use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LeaveQuotaOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Informasi Kuota Cuti Anda';

    public function table(Table $table): Table
    {
        return $table
            ->query(Typeofleave::query())
            ->columns([
                Tables\Columns\TextColumn::make('leaves_name')
                    ->label('Jenis Cuti')
                    ->icon('heroicon-o-calendar-days')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Total Kuota')
                    ->alignCenter()
                    ->state(function (Typeofleave $record) {
                        if (!$record->has_quota) {
                            return '∞';
                        }
                        
                        $quota = LeaveQuota::where('user_id', auth()->id())
                            ->where('typeofleave_id', $record->id)
                            ->where('year', now()->year)
                            ->first();
                            
                        return $quota ? $quota->total_days . ' hari' : '0 hari';
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('used_days')
                    ->label('Terpakai')
                    ->alignCenter()
                    ->state(function (Typeofleave $record) {
                        if ($record->has_quota) {
                            $quota = LeaveQuota::where('user_id', auth()->id())
                                ->where('typeofleave_id', $record->id)
                                ->where('year', now()->year)
                                ->first();
                            return $quota ? $quota->used_days . ' hari' : '0 hari';
                        }
                        
                        // For non-quota leaves, calculate from approved leave records
                        $approvedLeaves = Leave::where('user_id', auth()->id())
                            ->where('typeofleave_id', $record->id)
                            ->where('status', 'approved')
                            ->whereYear('start_date', now()->year)
                            ->get();
                            
                        $used = 0;
                        foreach ($approvedLeaves as $leave) {
                            $used += $leave->leaveDays();
                        }
                        
                        return $used . ' hari';
                    })
                    ->color('warning'),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Sisa')
                    ->alignCenter()
                    ->state(function (Typeofleave $record) {
                        if (!$record->has_quota) {
                            return '∞';
                        }
                        
                        $quota = LeaveQuota::where('user_id', auth()->id())
                            ->where('typeofleave_id', $record->id)
                            ->where('year', now()->year)
                            ->first();
                            
                        return $quota ? $quota->remaining_days . ' hari' : '0 hari';
                    })
                    ->color(function (Typeofleave $record) {
                        if (!$record->has_quota) return 'success';
                        
                        $quota = LeaveQuota::where('user_id', auth()->id())
                            ->where('typeofleave_id', $record->id)
                            ->where('year', now()->year)
                            ->first();
                            
                        return ($quota && $quota->remaining_days > 0) ? 'success' : 'danger';
                    })
                    ->weight('bold')
                    ->badge(fn (Typeofleave $record) => $record->has_quota), // Only badge if it has quota
            ])
            ->paginated(false);
    }
}

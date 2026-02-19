<?php

namespace App\Filament\Resources\MonthlyReportV2\Tables;

use App\Models\MonthlyReportV2;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MonthlyReportsV2Table
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Employees see only their own reports
                if (! Auth::user()->hasRole(['super_admin', 'admin'])) {
                     $query->where('user_id', Auth::id());
                }
            })
            ->columns([
                TextColumn::make('No')->rowIndex(),
                TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn ($state) => [
                        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                        '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                        '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                    ][$state] ?? $state)
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn () => ! Auth::user()->hasRole(['super_admin', 'admin'])),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'reviewed',
                        'success' => 'approved',
                    ]),
                TextColumn::make('photos_count')
                    ->counts('photos')
                    ->label('Foto'),
                TextColumn::make('employee_signed_at')
                    ->label('TTD Pegawai')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn () => MonthlyReportV2::distinct()->pluck('year', 'year')->toArray()),
                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                        '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                        '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('download_pdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (MonthlyReportV2 $record) => $record->status !== 'draft')
                    ->url(fn (MonthlyReportV2 $record) => route('monthly-report-v2.pdf', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}

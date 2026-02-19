<?php

namespace App\Filament\Resources\DailyReportV2\Tables;

use App\Models\DailyReportV2;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyReportsV2Table
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
                TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    // Only visible to admin/super_admin
                    ->visible(fn () => Auth::user()->hasRole(['super_admin', 'admin'])),

                TextColumn::make('scope_checks_count')
                    ->label('Ruang Lingkup')
                    ->counts('scopeChecks')
                    ->formatStateUsing(fn ($state, DailyReportV2 $record) => "{$state} item")
                    ->description(function (DailyReportV2 $record) {
                        // Optional: Show how many were checked vs total?
                        // "3/5 selesai"
                        $total = $record->scopeChecks()->count();
                        $checked = $record->scopeChecks()->where('is_checked', true)->count();
                        return "{$checked}/{$total} selesai";
                    }),

                TextColumn::make('photos_count')
                    ->label('Foto')
                    ->counts('photos')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                Filter::make('report_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->hasRole(['super_admin', 'admin'])),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}

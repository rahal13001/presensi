<?php

namespace App\Filament\Resources\MonthlyReportV2;

use App\Filament\Resources\MonthlyReportV2\Pages;
use App\Filament\Resources\MonthlyReportV2\Schemas\MonthlyReportV2Form;
use App\Filament\Resources\MonthlyReportV2\Tables\MonthlyReportsV2Table;
use App\Models\MonthlyReportV2;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MonthlyReportV2Resource extends Resource
{
    protected static ?string $model = MonthlyReportV2::class;

    protected static ?int $navigationSort = 8; // Per Implementation Plan

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan Bulanan';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-chart-bar';
    }

    public static function getLabel(): ?string
    {
        return app()->getLocale() === 'id' ? 'Laporan Bulanan (Baru)' : 'Monthly Report (New)';
    }

    public static function form(Schema $schema): Schema
    {
        return MonthlyReportV2Form::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonthlyReportsV2Table::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonthlyReportsV2::route('/'),
            'create' => Pages\CreateMonthlyReportV2::route('/create'),
            'view' => Pages\ViewMonthlyReportV2::route('/{record}'),
            'edit' => Pages\EditMonthlyReportV2::route('/{record}/edit'),
        ];
    }
    
    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                \Filament\Schemas\Components\Section::make('Informasi Laporan')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('month')
                            ->label('Bulan')
                            ->formatStateUsing(fn ($state) => [
                                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                                '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                                '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ][$state] ?? $state),
                        \Filament\Infolists\Components\TextEntry::make('year')
                            ->label('Tahun'),
                        \Filament\Infolists\Components\TextEntry::make('team_name')
                            ->label('Nama Tim'),
                        \Filament\Infolists\Components\TextEntry::make('team_leader_name')
                            ->label('Ketua Tim'),
                        \Filament\Infolists\Components\TextEntry::make('user.name')
                            ->label('Pegawai'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->colors([
                                'gray' => 'draft',
                                'warning' => 'submitted',
                                'info' => 'reviewed',
                                'success' => 'approved',
                            ]),
                    ])->columns(2),
                
                \Filament\Schemas\Components\Section::make('Ringkasan Ruang Lingkup')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('scope_summary_view')
                            ->label('Ringkasan Checklist')
                            ->state(function ($record) {
                                // Logic similar to Form placeholder
                                $monthlyReport = $record;
                                $userId = $monthlyReport->user_id;
                                $month = $monthlyReport->month;
                                $year = $monthlyReport->year;
                                
                                $dailyReports = \App\Models\DailyReportV2::with(['scopeChecks.scope', 'otherWorks.otherWorkOption'])
                                    ->where('user_id', $userId)
                                    ->whereMonth('report_date', $month)
                                    ->whereYear('report_date', $year)
                                    ->get();

                                if ($dailyReports->isEmpty()) {
                                    return 'Belum ada laporan harian untuk periode ini.';
                                }

                                $totalDays = $dailyReports->count();
                                $scopeCounts = [];
                                $otherWorkCounts = [];

                                foreach ($dailyReports as $report) {
                                    foreach ($report->scopeChecks as $check) {
                                        if ($check->is_checked) {
                                            $scopeName = $check->scope->name;
                                            if (! isset($scopeCounts[$scopeName])) {
                                                $scopeCounts[$scopeName] = 0;
                                            }
                                            $scopeCounts[$scopeName]++;
                                        }
                                    }
                                    
                                    foreach ($report->otherWorks as $work) {
                                        $workName = $work->otherWorkOption->name ?? 'Lainnya';
                                        if (! isset($otherWorkCounts[$workName])) {
                                            $otherWorkCounts[$workName] = 0;
                                        }
                                        $otherWorkCounts[$workName]++;
                                    }
                                }
                                
                                $html = "<div class='text-sm space-y-4'>";
                                $html .= "<div><p class='font-bold'>Total Hari Laporan: {$totalDays}</p>";
                                $html .= "<p class='font-semibold mt-2'>Ruang Lingkup (Scope):</p>";
                                $html .= "<ul class='list-disc pl-5'>";
                                foreach ($scopeCounts as $name => $count) {
                                    $html .= "<li>{$name}: <strong>{$count}/{$totalDays}</strong> hari</li>";
                                }
                                $html .= "</ul></div>";
                                
                                if (! empty($otherWorkCounts)) {
                                    $html .= "<div><p class='font-semibold mt-2'>Pekerjaan Lain:</p>";
                                    $html .= "<ul class='list-disc pl-5'>";
                                    foreach ($otherWorkCounts as $name => $count) {
                                        $html .= "<li>{$name}: <strong>{$count}</strong> kali</li>";
                                    }
                                    $html .= "</ul></div>";
                                }
                                
                                $html .= "</div>";

                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),

                \Filament\Schemas\Components\Section::make('Foto Dokumentasi')
                    ->schema([
                        // Display photos. 
                        // Infolists don't have a direct "Relation Image Gallery" component easily without checking plugins.
                        // But we can use ImageEntry with state from relationship.
                        \Filament\Infolists\Components\ImageEntry::make('photos.dailyPhoto.photo_path')
                            ->label('Foto Terpilih')
                            ->disk('public') // Assuming public disk
                            ->columns(5)
                            ->height(100)
                            ->extraImgAttributes([
                                'class' => 'rounded-md shadow-sm border border-gray-200 dark:border-gray-700 hover:scale-105 transition-transform duration-300',
                                'title' => 'Klik untuk memperbesar',
                            ])
                            ->url(fn ($state) => \Illuminate\Support\Facades\Storage::disk('public')->url($state))
                            ->openUrlInNewTab(),
                    ]),

                \Filament\Schemas\Components\Section::make('Tanda Tangan')
                    ->schema([
                         \Filament\Infolists\Components\TextEntry::make('city')
                            ->label('Kota'),
                         \Filament\Infolists\Components\TextEntry::make('signed_date')
                            ->label('Tanggal')
                            ->date('d/m/Y'),
                         \Filament\Infolists\Components\ImageEntry::make('employee_sign')
                            ->label('Tanda Tangan Pegawai')
                            ->height(100),
                         \Filament\Infolists\Components\TextEntry::make('employee_signed_at')
                            ->label('Ditandatangani Pada')
                            ->dateTime('d/m/Y H:i'),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Review Ketua Tim')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('teamLeader.name')
                            ->label('Ketua Tim'),
                        \Filament\Infolists\Components\TextEntry::make('condition_status')
                            ->label('Kondisi Umum')
                            ->badge()
                            ->colors([
                                'success' => 'baik',
                                'danger' => 'rusak',
                                'warning' => 'permasalahan',
                            ])
                            ->formatStateUsing(fn ($state) => [
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                                'permasalahan' => 'Ada Permasalahan',
                            ][$state] ?? $state),
                        \Filament\Infolists\Components\TextEntry::make('leader_notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        \Filament\Infolists\Components\ImageEntry::make('leader_sign')
                            ->label('Tanda Tangan Ketua Tim')
                            ->height(100),
                        \Filament\Infolists\Components\TextEntry::make('leader_signed_at')
                            ->label('Direview Pada')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && $record->team_leader_id),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery(); 
            // Filtering for specific users is best done in the Table's modifyQueryUsing 
            // if we want admins to see everything but users to see only their own in the list.
            // However, for security, a global scope or policy is better.
            // The requirement says: "Employees see only their own reports (modifyQueryUsing with user_id = Auth::id() for role user)"
            // So we'll handle that in the Table class or here.
    }
}

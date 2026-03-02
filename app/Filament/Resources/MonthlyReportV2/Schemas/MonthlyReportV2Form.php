<?php

namespace App\Filament\Resources\MonthlyReportV2\Schemas;

use App\Models\DailyPhoto;
use App\Models\DailyReportV2;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use App\Forms\Components\SignatureField;

class MonthlyReportV2Form
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Info Laporan')
                    ->disabled(fn ($record) => $record && $record->status !== 'draft')
                    ->schema([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret',
                                '4' => 'April', '5' => 'Mei', '6' => 'Juni',
                                '7' => 'Juli', '8' => 'Agustus', '9' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('selected_photos', [])) // Reset photos when month changes
                            ->native(false),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->length(4)
                            ->default(now()->year)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('selected_photos', [])), // Reset photos when year changes
                        Select::make('user_id')
                            ->label('Pegawai')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => Auth::id())
                            ->disabled(fn () => ! Auth::user()->hasAnyRole(['super_admin', 'admin']))
                            ->dehydrated() // Ensure value is saved even if disabled
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('selected_photos', [])),
                        Select::make('team_name')
                            ->label('Nama Tim')
                            ->options(fn () => \App\Models\Team::all()->pluck('team_name', 'team_name'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $team = \App\Models\Team::where('team_name', $state)->with('user')->first();
                                    $set('team_leader_name', $team?->user?->name);
                                } else {
                                    $set('team_leader_name', null);
                                }
                            }),
                        TextInput::make('team_leader_name')
                            ->label('Ketua Tim')
                            ->required()
                            ->readOnly()
                            ->dehydrated(),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('Ringkasan Ruang Lingkup')
                    ->schema([
                        Placeholder::make('scope_summary')
                            ->label('')
                            ->content(function ($get) {
                                $month = $get('month');
                                $year = $get('year');
                                $userId = $get('user_id') ?? Auth::id();

                                if (! $month || ! $year) {
                                    return 'Pilih Bulan dan Tahun untuk melihat ringkasan.';
                                }

                                $dailyReports = DailyReportV2::with(['scopeChecks.scope', 'otherWorks.otherWorkOption'])
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
                                        if ($check->is_checked && $check->scope) {
                                            $scopeName = $check->scope->name;
                                            $scopeCounts[$scopeName] = ($scopeCounts[$scopeName] ?? 0) + 1;
                                        }
                                    }
                                    foreach ($report->otherWorks as $work) {
                                        $workName = $work->otherWorkOption->name ?? 'Lainnya';
                                        $otherWorkCounts[$workName] = ($otherWorkCounts[$workName] ?? 0) + 1;
                                    }
                                }

                                // Build styled HTML with inline styles + <style> for dark mode
                                $html = "
<style>
    .form-scope .stat-card { display:flex; align-items:center; gap:12px; padding:16px 20px; border-radius:12px; margin-bottom:24px; background:#eff6ff; border:1px solid #bfdbfe; }
    .form-scope .stat-num { font-size:32px; font-weight:800; color:#2563eb; line-height:1; }
    .form-scope .stat-lbl { font-size:14px; font-weight:600; color:#2563eb; }
    .form-scope .stat-sub { font-size:11px; color:#60a5fa; }
    .form-scope .sec-title { font-size:15px; font-weight:700; margin-bottom:12px; padding-bottom:8px; }
    .form-scope .sec-title.blue { color:#2563eb; border-bottom:2px solid #93c5fd; }
    .form-scope .sec-title.green { color:#16a34a; border-bottom:2px solid #86efac; }
    .form-scope table { width:100%; border-collapse:separate; border-spacing:0 6px; }
    .form-scope th { text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; padding:0 12px 4px; }
    .form-scope th:last-child { text-align:right; }
    .form-scope .rn { padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; border-right:none; border-radius:8px 0 0 8px; font-size:13px; font-weight:700; color:#6b7280; width:40px; text-align:center; vertical-align:top; }
    .form-scope .ra { padding:10px 12px; background:#f9fafb; border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; font-size:13px; font-weight:500; color:#1f2937; line-height:1.5; vertical-align:top; }
    .form-scope .rb { padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; border-left:none; border-radius:0 8px 8px 0; text-align:right; vertical-align:top; white-space:nowrap; }
    .form-scope .bb { display:inline-block; padding:3px 10px; border-radius:20px; background:#dbeafe; color:#1d4ed8; font-size:12px; font-weight:700; }
    .form-scope .bg { display:inline-block; padding:3px 10px; border-radius:20px; background:#dcfce7; color:#15803d; font-size:12px; font-weight:700; }
    .form-scope .sec-gap { margin-bottom:24px; }
    .dark .form-scope .stat-card { background:rgba(37,99,235,0.12); border-color:rgba(37,99,235,0.3); }
    .dark .form-scope .stat-num { color:#60a5fa; }
    .dark .form-scope .stat-lbl { color:#93c5fd; }
    .dark .form-scope .stat-sub { color:#60a5fa; }
    .dark .form-scope .sec-title.blue { color:#60a5fa; border-bottom-color:rgba(59,130,246,0.3); }
    .dark .form-scope .sec-title.green { color:#4ade80; border-bottom-color:rgba(34,197,94,0.3); }
    .dark .form-scope th { color:#9ca3af; }
    .dark .form-scope .rn { background:rgba(255,255,255,0.03); border-color:rgba(255,255,255,0.08); color:#9ca3af; }
    .dark .form-scope .ra { background:rgba(255,255,255,0.03); border-color:rgba(255,255,255,0.08); color:#e5e7eb; }
    .dark .form-scope .rb { background:rgba(255,255,255,0.03); border-color:rgba(255,255,255,0.08); }
    .dark .form-scope .bb { background:rgba(59,130,246,0.15); color:#93c5fd; }
    .dark .form-scope .bg { background:rgba(34,197,94,0.15); color:#86efac; }
</style>
<div class='form-scope'>";

                                // Total days header
                                $html .= "<div class='stat-card'><div class='stat-num'>{$totalDays}</div><div><div class='stat-lbl'>Hari Laporan</div><div class='stat-sub'>Total laporan harian dalam periode ini</div></div></div>";

                                // Scopes table
                                $html .= "<div class='sec-gap'><div class='sec-title blue'>📋 Ruang Lingkup (Scope)</div>";
                                $html .= "<table><thead><tr><th>No</th><th>Aktivitas</th><th>Frekuensi</th></tr></thead><tbody>";
                                $i = 1;
                                foreach ($scopeCounts as $name => $count) {
                                    $html .= "<tr><td class='rn'>{$i}</td><td class='ra'>{$name}</td><td class='rb'><span class='bb'>{$count}/{$totalDays} hari</span></td></tr>";
                                    $i++;
                                }
                                $html .= "</tbody></table></div>";

                                // Other works table
                                if (! empty($otherWorkCounts)) {
                                    $html .= "<div><div class='sec-title green'>💼 Pekerjaan Lain</div>";
                                    $html .= "<table><thead><tr><th>No</th><th>Aktivitas</th><th>Frekuensi</th></tr></thead><tbody>";
                                    $j = 1;
                                    foreach ($otherWorkCounts as $name => $count) {
                                        $html .= "<tr><td class='rn'>{$j}</td><td class='ra'>{$name}</td><td class='rb'><span class='bg'>{$count}x</span></td></tr>";
                                        $j++;
                                    }
                                    $html .= "</tbody></table></div>";
                                }

                                $html .= "</div>";

                                return new HtmlString($html);
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make('Foto Dokumentasi')
                    ->disabled(fn ($record) => $record && $record->status !== 'draft')
                    ->description('Pilih tepat 10 foto dari laporan harian Anda untuk bulan ini.')
                    ->schema([
                        CheckboxList::make('selected_photos') // Virtual field
                            ->label('Pilih Foto')
                            ->options(function ($get) {
                                $month = $get('month');
                                $year = $get('year');
                                $userId = $get('user_id') ?? Auth::id();

                                if (! $month || ! $year) {
                                    return [];
                                }

                                return DailyPhoto::whereHas('dailyReport', function ($query) use ($userId, $month, $year) {
                                    $query->where('user_id', $userId)
                                        ->whereMonth('report_date', $month)
                                        ->whereYear('report_date', $year);
                                })
                                ->with('dailyReport') // Eager load dailyReport
                                ->get()
                                ->mapWithKeys(function ($photo) {
                                    $date = $photo->dailyReport->report_date->format('d/m/Y');
                                    $imageUrl = asset('storage/' . $photo->photo_path);
                                    
                                    $label = "
                                        <div class='flex flex-col h-full'>
                                            <div class='relative w-full aspect-video mb-2 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800'>
                                                <img src='{$imageUrl}' 
                                                     alt='Foto' 
                                                     class='absolute inset-0 w-full h-full object-cover' 
                                                     loading='lazy' />
                                            </div>
                                            <div class='flex flex-col'>
                                                <span class='font-medium text-xs'>{$date}</span>
                                            </div>
                                        </div>
                                    ";
                                    return [$photo->id => $label];
                                });
                            })
                            ->allowHtml()
                            ->columns([
                                'default' => 2,
                                'sm' => 3,
                                'md' => 4,
                                'xl' => 5,
                            ])
                            ->gridDirection('row')
                            ->minItems(10) // Enforce 10
                            ->maxItems(10)
                            ->validationMessages([
                                'min_items' => 'Anda harus memilih tepat 10 foto.',
                                'max_items' => 'Anda harus memilih tepat 10 foto.',
                            ])
                            ->live()
                            ->required(),
                    ])
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Tanda Tangan')
                            ->disabled(fn ($record) => $record && $record->status !== 'draft')
                            ->schema([
                                TextInput::make('city')
                                    ->label('Kota')
                                    ->default('Sorong')
                                    ->required(),
                                DatePicker::make('signed_date')
                                    ->label('Tanggal Tanda Tangan')
                                    ->default(now())
                                    ->required()
                                    ->native(false),
                                SignatureField::make('employee_sign')
                                    ->label('Tanda Tangan Pegawai')
                                    ->penColor('#000')
                                    ->lineWidth(3.5)
                                    ->canvasHeight(200)
                                    ->required()
                                    ->columnSpan('full'),
                            ])->columns(2),
                    ])->columnSpan('full'),
            ]);
    }
}

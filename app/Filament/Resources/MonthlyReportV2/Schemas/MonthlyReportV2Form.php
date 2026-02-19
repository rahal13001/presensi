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
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

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
                    ])->columns(2),

                Section::make('Ringkasan Ruang Lingkup')
                    ->schema([
                        Placeholder::make('scope_summary')
                            ->label('Ringkasan Checklist Harian')
                            ->content(function ($get) {
                                $month = $get('month');
                                $year = $get('year');
                                $userId = $get('user_id') ?? Auth::id();

                                if (! $month || ! $year) {
                                    return 'Pilih Bulan dan Tahun untuk melihat ringkasan.';
                                }

                                // Fetch daily reports for this user/month/year
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

                                return new HtmlString($html);
                            }),
                    ]),

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
                                    $caption = $photo->caption ?? 'No Caption';
                                    $imageUrl = asset('storage/' . $photo->photo_path);
                                    
                                    $label = "
                                        <div class='flex flex-col h-full'>
                                            <div class='relative w-full aspect-video mb-2 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800'>
                                                <img src='{$imageUrl}' 
                                                     alt='{$caption}' 
                                                     class='absolute inset-0 w-full h-full object-cover' 
                                                     loading='lazy' />
                                            </div>
                                            <div class='flex flex-col'>
                                                <span class='font-medium text-xs'>{$date}</span>
                                                <span class='text-xs text-gray-500 truncate' title='{$caption}'>{$caption}</span>
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
                    ]),

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
                                SignaturePad::make('employee_sign')
                                    ->label('Tanda Tangan Pegawai')
                                    ->dotSize(2.0)
                                    ->lineMinWidth(0.5)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->exportPenColor('#000')
                                    ->velocityFilterWeight(0.7)
                                    ->required()
                                    ->columnSpan('full'),
                            ])->columns(2),
                    ])->columnSpan('full'),
            ]);
    }
}

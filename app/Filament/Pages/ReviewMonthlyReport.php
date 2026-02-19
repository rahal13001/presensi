<?php

namespace App\Filament\Pages;

use App\Models\MonthlyReportV2;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;


class ReviewMonthlyReport extends Page implements HasTable
{
    use InteractsWithTable;
    use \Filament\Resources\Concerns\HasTabs;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationSort(): ?int
    {
        return 9;
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Review Laporan Bulanan';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan Bulanan';
    }
    
    // Use default page view to allow content() schema rendering
    // protected string $view = 'filament.pages.review-monthly-report';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole(['super_admin', 'admin']) || $user->hasRole('kepala'));
    }

    public function getTabs(): array
    {
        return [
            'pending' => \Filament\Schemas\Components\Tabs\Tab::make('Menunggu Review')
                ->badge(MonthlyReportV2::where('status', 'submitted')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'submitted')),
            'history' => \Filament\Schemas\Components\Tabs\Tab::make('Riwayat Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('Semua')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['submitted', 'approved'])),
        ];
    }

    public function content(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                $this->getTabsContentComponent(),
                \Filament\Schemas\Components\EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MonthlyReportV2::query()
                    ->latest('updated_at')
            )
            ->modifyQueryUsing($this->modifyQueryWithActiveTab(...))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'approved',
                    ]),
                TextColumn::make('employee_signed_at') // or updated_at
                    ->label('Tanggal Submit')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Review Laporan Bulanan')
                    ->modalWidth('7xl')
                    ->modalContent(fn (MonthlyReportV2 $record) => view('filament.pages.review-monthly-report-modal', ['record' => $record]))
                    ->visible(fn (MonthlyReportV2 $record) => $record->status === 'submitted')
                    ->form([
                        Radio::make('condition_status')
                            ->label('Kondisi Umum')
                            ->options([
                                'baik' => 'Baik (Good)',
                                'rusak' => 'Rusak (Damaged)',
                                'permasalahan' => 'Ada Permasalahan (Issues Found)',
                            ])
                            ->required(),
                        Textarea::make('leader_notes')
                            ->label('Catatan Ketua Tim')
                            ->rows(3),
                        SignaturePad::make('leader_sign')
                            ->label('Tanda Tangan Ketua Tim')
                            ->dotSize(2.0)
                            ->lineMinWidth(0.5)
                            ->lineMaxWidth(2.5)
                            ->throttle(16)
                            ->minDistance(5)
                            ->exportPenColor('#000') // Black color
                            ->velocityFilterWeight(0.7)
                            ->required(),
                    ])
                    ->action(function (MonthlyReportV2 $record, array $data) {
                        $record->update([
                            'team_leader_id' => Auth::id(),
                            'condition_status' => $data['condition_status'],
                            'leader_notes' => $data['leader_notes'],
                            'leader_sign' => $data['leader_sign'],
                            'leader_signed_at' => now(),
                            'status' => 'approved',
                        ]);

                        Notification::make()
                            ->title('Laporan Disetujui')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Setujui Laporan'),
                
                Action::make('view_approved')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Laporan Bulanan')
                    ->modalWidth('7xl')
                    ->modalContent(fn (MonthlyReportV2 $record) => view('filament.pages.review-monthly-report-modal', ['record' => $record]))
                    ->visible(fn (MonthlyReportV2 $record) => $record->status === 'approved')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}

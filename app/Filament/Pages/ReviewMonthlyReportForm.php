<?php

namespace App\Filament\Pages;

use App\Models\MonthlyReportV2;
use App\Forms\Components\SignatureField;
use Filament\Pages\Page;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ReviewMonthlyReportForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.review-monthly-report-form';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'review-report';

    public ?int $recordId = null;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->hasRole(['super_admin', 'admin']) || $user->hasRole('kepala'))) {
            abort(403);
        }

        $this->recordId = (int) request()->query('id');
        
        if (! $this->recordId) {
            abort(404);
        }

        $record = $this->getRecord();

        if (! $record) {
            abort(404);
        }

        if ($record->status !== 'submitted') {
            Notification::make()
                ->warning()
                ->title('Laporan ini tidak dalam status menunggu review.')
                ->send();

            $this->redirect(ReviewMonthlyReport::getUrl());
            return;
        }

        $this->form->fill();
    }

    public function getRecord(): ?MonthlyReportV2
    {
        return MonthlyReportV2::with(['user', 'photos.dailyPhoto'])->find($this->recordId);
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Review Laporan Bulanan';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
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
                SignatureField::make('leader_sign')
                    ->label('Tanda Tangan Ketua Tim')
                    ->penColor('#000')
                    ->lineWidth(3.5)
                    ->canvasHeight(200)
                    ->required(),
            ]);
    }

    public function approve(): void
    {
        $data = $this->form->getState();
        $record = $this->getRecord();

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

        $this->redirect(ReviewMonthlyReport::getUrl());
    }
}

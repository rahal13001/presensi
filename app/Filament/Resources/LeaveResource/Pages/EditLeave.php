<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeave extends EditRecord
{
    protected static string $resource = LeaveResource::class;

    // Track the original state before saving
    private ?string $originalStatus = null;
    private ?int $originalUserId = null;
    private ?int $originalTypeofleaveId = null;
    private $originalStartDate = null;
    private $originalEndDate = null;

    protected function beforeSave(): void
    {
        $record = $this->record;
        $this->originalStatus = $record->getOriginal('status');
        $this->originalUserId = $record->getOriginal('user_id');
        $this->originalTypeofleaveId = $record->getOriginal('typeofleave_id');
        $this->originalStartDate = $record->getOriginal('start_date');
        $this->originalEndDate = $record->getOriginal('end_date');
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $newStatus = $record->status;

        // CASE 1: Status changed FROM approved → REFUND old quota
        if ($this->originalStatus === 'approved' && $newStatus !== 'approved') {
            \App\Models\Leave::refundQuota($this->originalUserId, $this->originalTypeofleaveId, $this->originalStartDate, $this->originalEndDate);
            \App\Models\Leave::unmarkAttendance($this->originalUserId, $this->originalStartDate, $this->originalEndDate);
        }

        // CASE 2: Status changed TO approved → DEDUCT new quota
        if ($this->originalStatus !== 'approved' && $newStatus === 'approved') {
            \App\Models\Leave::deductQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
            \App\Models\Leave::markAttendance($record->user_id, $record->start_date, $record->end_date);
        }

        // CASE 3: Status remains approved but user/type/dates changed → REFUND old, DEDUCT new
        if ($this->originalStatus === 'approved' && $newStatus === 'approved') {
            $changed = $this->originalUserId != $record->user_id
                || $this->originalTypeofleaveId != $record->typeofleave_id
                || \Carbon\Carbon::parse($this->originalStartDate)->ne(\Carbon\Carbon::parse($record->start_date))
                || \Carbon\Carbon::parse($this->originalEndDate)->ne(\Carbon\Carbon::parse($record->end_date));

            if ($changed) {
                // Refund old
                \App\Models\Leave::refundQuota($this->originalUserId, $this->originalTypeofleaveId, $this->originalStartDate, $this->originalEndDate);
                \App\Models\Leave::unmarkAttendance($this->originalUserId, $this->originalStartDate, $this->originalEndDate);
                // Deduct new
                \App\Models\Leave::deductQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                \App\Models\Leave::markAttendance($record->user_id, $record->start_date, $record->end_date);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            // Approval Action
            \Filament\Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (\App\Models\Leave $record) => $record->status === 'pending' && auth()->user()->hasAnyRole(['super_admin', 'kepala']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('note')
                        ->label('Catatan (Opsional)'),
                    \Filament\Forms\Components\Select::make('approved_by_name')
                        ->label('Disetujui Oleh')
                        ->options(function () {
                            return \App\Models\User::role('kepala')->pluck('name', 'name');
                        })
                        ->searchable()
                        ->visible(fn () => auth()->user()->hasRole('super_admin'))
                ])
                ->action(function (\App\Models\Leave $record, array $data) {
                    $record->update([
                        'status' => 'approved',
                        'note' => $data['note'] ?? null,
                        'approved_by_name' => auth()->user()->hasRole('super_admin') && !empty($data['approved_by_name']) ? $data['approved_by_name'] : auth()->user()->name,
                        'approved_at' => now(),
                    ]);
                    
                    // Explicitly deduct quota and mark attendance
                    \App\Models\Leave::deductQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                    \App\Models\Leave::markAttendance($record->user_id, $record->start_date, $record->end_date);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Cuti Anda Disetujui')
                        ->body("Pengajuan {$record->typeofleave->leaves_name} Anda telah disetujui oleh {$record->approved_by_name}.")
                        ->success()
                        ->sendToDatabase($record->user);
                        
                    $this->refreshFormData(['status', 'approved_by_name', 'approved_at', 'note']);
                }),
                
            // Rejection Action
            \Filament\Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn (\App\Models\Leave $record) => $record->status === 'pending' && auth()->user()->hasAnyRole(['super_admin', 'kepala']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('note')
                        ->label('Alasan Penolakan')
                        ->required(),
                    \Filament\Forms\Components\Select::make('approved_by_name')
                        ->label('Ditolak Oleh')
                        ->options(function () {
                            return \App\Models\User::role('kepala')->pluck('name', 'name');
                        })
                        ->searchable()
                        ->visible(fn () => auth()->user()->hasRole('super_admin'))
                ])
                ->action(function (\App\Models\Leave $record, array $data) {
                    // If previously approved, refund quota first
                    if ($record->getOriginal('status') === 'approved') {
                        \App\Models\Leave::refundQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                        \App\Models\Leave::unmarkAttendance($record->user_id, $record->start_date, $record->end_date);
                    }
                    
                    $record->update([
                        'status' => 'rejected',
                        'note' => $data['note'],
                        'approved_by_name' => auth()->user()->hasRole('super_admin') && !empty($data['approved_by_name']) ? $data['approved_by_name'] : auth()->user()->name,
                        'approved_at' => now(),
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Cuti Anda Ditolak')
                        ->body("Pengajuan {$record->typeofleave->leaves_name} Anda ditolak oleh {$record->approved_by_name}. Alasan: {$data['note']}")
                        ->danger()
                        ->sendToDatabase($record->user);
                        
                    $this->refreshFormData(['status', 'approved_by_name', 'approved_at', 'note']);
                }),
                
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->hasRole('super_admin'))
                ->before(function (\App\Models\Leave $record) {
                    if ($record->status === 'approved') {
                        \App\Models\Leave::refundQuota($record->user_id, $record->typeofleave_id, $record->start_date, $record->end_date);
                        \App\Models\Leave::unmarkAttendance($record->user_id, $record->start_date, $record->end_date);
                    }
                }),
        ];
    }
}

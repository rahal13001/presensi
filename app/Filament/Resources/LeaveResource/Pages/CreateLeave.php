<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use App\Models\LeaveQuota;
use App\Models\Typeofleave;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Use form-selected user_id if available (admin/kepala creating for others),
        // otherwise default to the authenticated user
        if (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }
        
        $typeofleave = Typeofleave::find($data['typeofleave_id']);
        
        // Quota check logic
        if ($typeofleave && $typeofleave->has_quota) {
            $year = Carbon::parse($data['start_date'])->year;
            $leaveDays = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
            
            $quota = LeaveQuota::where('user_id', $data['user_id'])
                ->where('typeofleave_id', $typeofleave->id)
                ->where('year', $year)
                ->first();
                
            if (!$quota) {
                Notification::make()
                    ->title('Kuota Belum Tersedia')
                    ->body('Pegawai tidak memiliki kuota ' . $typeofleave->leaves_name . ' untuk tahun ' . $year . '.')
                    ->danger()
                    ->send();
                $this->halt();
            }
            
            if ($quota->remaining_days < $leaveDays) {
                Notification::make()
                    ->title('Sisa Kuota Tidak Cukup')
                    ->body('Sisa kuota ' . $typeofleave->leaves_name . ': ' . $quota->remaining_days . ' hari. Diajukan: ' . $leaveDays . ' hari.')
                    ->danger()
                    ->send();
                $this->halt();
            }
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $leave = $this->record;
        
        // If created directly with approved status, deduct quota immediately
        if ($leave->status === 'approved') {
            \App\Models\Leave::deductQuota($leave->user_id, $leave->typeofleave_id, $leave->start_date, $leave->end_date);
            \App\Models\Leave::markAttendance($leave->user_id, $leave->start_date, $leave->end_date);
        }
        
        // Find team leaders for the employee's teams
        $teamLeaders = \App\Models\User::whereIn('id', function($query) use ($leave) {
            $query->select('user_id')
                ->from('teams')
                ->whereIn('id', function($sub) use ($leave) {
                    $sub->select('team_id')
                        ->from('team_user')
                        ->where('user_id', $leave->user_id);
                });
        })->get();
        
        if ($teamLeaders->count() > 0) {
            Notification::make()
                ->title('Pengajuan Cuti Baru')
                ->body("{$leave->user->name} mengajukan {$leave->typeofleave->leaves_name} dari {$leave->start_date->format('d M')} s/d {$leave->end_date->format('d M')}.")
                ->info()
                ->sendToDatabase($teamLeaders);
        }
    }
}

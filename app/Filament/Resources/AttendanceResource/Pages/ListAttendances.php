<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {

        $user = User::where('id', Auth::user()->id)->with('position')->first();

        if ($user->position->group == 'umum') {
            return [
                Action::make('Download Data')
                    ->url(route('attendance-export'))
                    ->color('primary'),
                Action::make('Tambah Presensi')
                    ->url(route('presensi'))
                    ->color('success'),
               
    
                Actions\CreateAction::make(),
            ];
        } else {
            return [
                Action::make('Download Data')
                    ->url(route('attendance-export'))
                    ->color('primary'),
                Action::make('Tambah Presensi')
                    ->url(route('shiftpresensi'))
                    ->color('success'),
                Actions\CreateAction::make(),
            ];
        }

        
    }
}

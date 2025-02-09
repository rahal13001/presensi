<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Schedule;
use App\Models\Attendance;
// use Auth;
use App\Models\Leave;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Presensi extends Component
{
    public $latitude;
    public $longitude;
    public $insideRadius = false;
    public $accuracy;

    public function mount(){

        
        $user = Auth::user();
        if(!$user->hasRole('user')) {
            return;
        }

        if ($user->position->group == 'shift') {
            return redirect()->route('shiftpresensi');
        }

    }
    public function render()
    {
       
        //ambil user ID dari auth
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        $attendance = Attendance::where('user_id', Auth::user()->id)
                            ->whereDate('created_at', date('Y-m-d'))->first();
        // dd($schedule);
        
        return view('livewire.presensi', [
            'schedule' => $schedule,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance
        ]);
    }

    public function store() 
    {
        $this->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'accuracy' => 'required'
        ]);

        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        $today = Carbon::today()->format('Y-m-d');
        //cek sedang cuti atau tidak
        $approvedLeave = Leave::where('user_id', Auth::user()->id)
                              ->where('status', 'approved')
                              ->whereDate('start_date', '<=', $today)
                              ->whereDate('end_date', '>=', $today)
                              ->exists();

        if ($approvedLeave) {
            session()->flash('error', 'Anda tidak dapat melakukan presensi karena sedang cuti.');
            return;
        }



        if ($this->latitude == null || $this->longitude == null) {
            session()->flash('error', 'Gagal mendapatkan lokasi.');
            return;
        }
        
        // Detect Fake GPS (accuracy < 7 meters)
        if ($this->insideRadius === false) {
            session()->flash('error', 'Presensi ditolak! Fake GPS terdeteksi.');
            return;
        }

        if ($schedule) {
            // dd($this->accuracy);
            $attendance = Attendance::where('user_id', Auth::user()->id)
                 ->whereDate('created_at', date('Y-m-d'))->first();
            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => Auth::user()->id,
                    'schedule_latitude' => $schedule->office->latitude,
                    'schedule_longitude' => $schedule->office->longitude,
                    'schedule_start_time' => $schedule->shift->start_time,
                    'schedule_end_time' => $schedule->shift->end_time,
                    'start_latitude' => $this->latitude,
                    'start_longitude' => $this->longitude,
                    'start_time' => Carbon::now()->toTimeString(),
                    // 'end_time' => Carbon::now()->toTimeString(),
                    'start_accuracy' => $this->accuracy,
                ]);
            } else {
                $attendance->update([
                    'end_latitude' => $this->latitude,
                    'end_longitude' => $this->longitude,
                    'end_time' => Carbon::now()->toTimeString(),
                    'end_accuracy' => $this->accuracy,
                ]);
            }
            
            return redirect('admin/attendances');

            // return redirect()->route('presensi', [
            //     'schedule' => $schedule,
            //     'insideRadius' => false
            // ]);
            
        }
    }
}

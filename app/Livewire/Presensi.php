<?php

namespace App\Livewire;

use App\Models\Leave;
use Livewire\Component;
// use Auth;
use App\Models\Schedule;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
       
        //ambil schedule dari auth
        $schedule = Schedule::where('user_id', Auth::user()->id)->with('wfaday')->first();
            if (!$schedule) {
                return;
        }
        $attendance = Attendance::where('user_id', Auth::user()->id)
                            ->whereDate('created_at', date('Y-m-d'))->first();


        // dd($schedule);

      

        $schedule = Schedule::where('user_id', Auth::user()->id)->with('wfaday')->first();
        if (!$schedule) {
            return;
        }

        $today = Carbon::now()->format('l'); // ✅ Example: "Wednesday"
        $wfaDays = $schedule->wfaday->pluck('day_name')->toArray(); // ✅ Get all WFA days

        // ✅ If today's day is in WFA days OR is_wfa is already set, set isWFA = true
        $isWFA = in_array( $today, $wfaDays) || $schedule->is_wfa;

        // dump($today, $wfaDays);
        // die();
        
        return view('livewire.presensi', [
            'schedule' => $schedule,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance,
            'isWFA' => $isWFA, // ✅ Pass to the Blade view
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
                 ->whereDate('start_date', date('Y-m-d'))->first();
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
                    'start_date' => Carbon::now()->toDateString(),     
                    'end_date' => Carbon::now()->toDateString(),   
                    // 'end_time' => Carbon::now()->toTimeString(),
                    'start_accuracy' => $this->accuracy,
                ]);
            } 
            // else {
            //     $attendance->update([
            //         'end_latitude' => $this->latitude,
            //         'end_longitude' => $this->longitude,
            //         'end_time' => Carbon::now()->toTimeString(),
            //         'end_accuracy' => $this->accuracy,
            //     ]);
            // }
            
            return redirect('admin/attendances');

            // return redirect()->route('presensi', [
            //     'schedule' => $schedule,
            //     'insideRadius' => false
            // ]);
            
        }
    }
    public function store_end() 
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
                    'end_latitude' => $this->latitude,
                    'end_longitude' => $this->longitude,
                    'start_date' => Carbon::now()->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                    // 'start_time' => Carbon::now()->toTimeString(),
                    'end_time' => Carbon::now()->toTimeString(),
                    'start_accuracy' => $this->accuracy,
                ]);
            } 
            else {
                $attendance->update([
                    'end_latitude' => $this->latitude,
                    'end_longitude' => $this->longitude,
                    'end_time' => Carbon::now()->toTimeString(),
                    'end_date' => Carbon::now()->toDateString(),
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

<?php

namespace App\Livewire;

use App\Models\Leave;
use Livewire\Component;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Shiftschedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Shiftpresensi extends Component
{
    public $latitude;
    public $longitude;
    public $insideRadius = false;
    public $accuracy;
    public $shiftschedule_id;
    public $selectedshiftschedule;
    public $shiftschedules; // Store dynamically updated shift schedules

    public function mount(){
        //ambil user ID dari auth
        $this->shiftschedules = Shiftschedule::with('shift','office')->get();

        $user = Auth::user();
        if($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('kepala')) {
            return;
        }
        
        if ($user->position->group == 'umum') {
            return redirect()->route('presensi');
        }
    }

    public function render()
    {
        //cek user
       
        $schedules = Shiftschedule::get();
        $attendance = Attendance::where('user_id', Auth::user()->id)
                            ->latest()->first();
        // dd($schedule);
        
        return view('livewire.shiftpresensi', [
            'shiftschedules' => $this->shiftschedules,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance,
            'schedules' => $schedules,
        ]);
    }

    public function UpdatedSelectedshiftschedule($selectedShiftschedule) {

        $this->shiftschedules = Shiftschedule::where('id', $selectedShiftschedule)->with('shift','office')->first();
       
        
        $this->shiftschedule_id = $this->shiftschedules->id;
      
    }

    public function store_start() 
    {
        $this->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'accuracy' => 'required'
        ]);

        $shiftschedule = Shiftschedule::where('id', $this->shiftschedule_id)->with('shift','office')->first();

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

        
            // dd($this->accuracy);
            // $attendance = Attendance::where('user_id', Auth::user()->id)->first();
            Attendance::create([
                    'user_id' => Auth::user()->id,
                    'schedule_latitude' => $shiftschedule->office->latitude,
                    'schedule_longitude' => $shiftschedule->office->longitude,
                    'schedule_start_time' => $shiftschedule->shift->start_time,
                    'schedule_end_time' => $shiftschedule->shift->end_time,
                    'start_latitude' => $this->latitude,
                    'start_longitude' => $this->longitude,
                    'start_time' => Carbon::now()->toTimeString(),
                    // 'end_time' => Carbon::now()->toTimeString(),
                    'start_accuracy' => $this->accuracy,
                ]);
            
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

    public function store_end() 
    {
       
        $attendance = Attendance::where('user_id', Auth::user()->id)->latest()->first();
        if ($attendance->end_time) {
            session()->flash('error', 'Gagal Presensi Pulang, Anda Belum Presensi Masuk.');
            return;
        }
        
        $attendance->update([
            'end_latitude' => $this->latitude,
            'end_longitude' => $this->longitude,
            'end_time' => Carbon::now()->toTimeString(),
            'end_accuracy' => $this->accuracy,
        ]);
        return redirect('admin/attendances');
    }

}

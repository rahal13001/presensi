<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Leave;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\Monthlyreport;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendancedataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Monthlyreport $monthlyreport)
    {
        
        $attendances = Attendance::where('user_id', $monthlyreport->user_id)->with('user', 'position', 'dailyreports')
        ->whereYear('created_at', (int) $monthlyreport->year)
        ->whereMonth('created_at', (int) $monthlyreport->month)
        ->get()
        ->map(function ($attendance) {
            $scheduleStartTime = Carbon::parse($attendance->schedule_start_time);
            $attendanceDate = Carbon::parse($attendance->created_at);
            $scheduleEndTime = Carbon::parse($attendance->schedule_end_time);

            // if not fill the start and end time
            if (is_null($attendance->start_time) && is_null($attendance->end_time)) {

                if ($attendance->is_leave) {
                    // Search for leave within the same date range in the `leaves` table
                    $leave = Leave::where('user_id', $attendance->user_id)->with('typeofleave')
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $attendanceDate)
                        ->whereDate('end_date', '>=', $attendanceDate)
                        ->first();
          
                    $attendance->typeofleave = $leave ? $leave->typeofleave->leaves_name : 'Tidak Diketahui';
                    $attendance->leave_reason = $leave ? $leave->reason : 'Tidak Diketahui';  // Add leave reason
                } else {
                    $attendance->leave_reason = '-';  // No leave on this date
                    $attendance->late_duration = "Tidak Hadir";
                    $attendance->attendance = "Tidak Hadir";
                    $attendance->work_duration = "0 Jam 0 Menit";

                }


            } else {
                $attendance->attendance = "Hadir";
                $startTime = is_null($attendance->start_time)
                    ? $scheduleStartTime->copy()->addHours(4)  // Punishment: Assume 4-hour late start
                    : Carbon::parse($attendance->start_time);
    
                $endTime = is_null($attendance->end_time)
                    ? $startTime->copy()->addHours(4)  // Cap work duration at 4 hours
                    : Carbon::parse($attendance->end_time);
    
                if ($endTime->lt($startTime)) {
                    $endTime->addDay();
                }
    
                // Calculate total work time
                $totalMinutes = $startTime->diffInMinutes($endTime);
                $hours = intdiv($totalMinutes, 60);
                $minutes = $totalMinutes % 60;
                $attendance->work_duration = "{$hours} Jam {$minutes} Menit";
    
                // Calculate lateness only if start_time is after schedule_start_time
                if ($startTime->greaterThan($scheduleStartTime)) {
                    $lateMinutes = $scheduleStartTime->diffInMinutes($startTime);
                    $lateHours = intdiv($lateMinutes, 60);
                    $remainingMinutes = $lateMinutes % 60;
                    $attendance->late_duration = "{$lateHours} Jam {$remainingMinutes} Menit";
                    $attendance->is_late = "Terlambat";
                } else {
                    $attendance->late_duration = "0 Jam 0 Menit";  // No lateness
                    $attendance->is_late = "";
                }

                if ($endTime->lt($scheduleEndTime)) {
                    $attendance->psw_status = 'PSW';  // Mark as PSW
                } else {
                    $attendance->psw_status = '-';  // No PSW
                }
            }
            
            return $attendance;
        });

        $team = Team::where('user_id', $monthlyreport->user_id)->with('user')->first();

       

        if ($attendances->isEmpty()) {
            return back()->with('error', 'Tidak ada data presensi untuk bulan ini.');
        }
        


        $pdf = Pdf::loadView('pdf.datapresensi', compact('attendances', 'monthlyreport', 'team'));
                return $pdf->setPaper('a4', 'landscape')->download('Data_Presensi_' . $monthlyreport->user->name . '..pdf');
    }
}

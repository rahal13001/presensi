<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Monthlyreport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DailytaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Monthlyreport $monthlyreport)
    {
            // Debug: Check what values are being used
                        
            $attendances = Attendance::where('user_id', $monthlyreport->user_id)->with('user', 'position', 'dailyreports')
                ->whereYear('created_at', (int) $monthlyreport->year)
                ->whereMonth('created_at', (int) $monthlyreport->month)

                ->get();

                // if ($attendances->isEmpty()) {
                //     return back()->with('error', 'Tidak ada data presensi untuk bulan ini.');
                // }
            
                $pdf = Pdf::loadView('pdf.tugasharian', compact('attendances', 'monthlyreport'));
                return $pdf->setPaper('a4', 'landscape')->download('Laporan_Tugas_Harian_' . $monthlyreport->user->name . '..pdf');
    }
}

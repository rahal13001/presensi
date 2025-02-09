<?php

use App\Livewire\Presensi;
use App\Livewire\Shiftpresensi;
use App\Exports\AttendanceExport;
use App\Http\Controllers\DailytaskController;
use App\Http\Middleware\CheckUserGroup;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function() {
    Route::get('presensi', Presensi::class)->name('presensi');
    Route::get('shiftpresensi', Shiftpresensi::class)->name('shiftpresensi');
    Route::get('attendance/export', function () {
        return Excel::download(new AttendanceExport, 'attendances.xlsx');
    })->name('attendance-export');
    
    
});

Route::get('/login', function() {
    return redirect('admin/login');
})->name('login');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdftugasharian/{monthlyreport}', DailytaskController::class)->name('pdftugasharian');


Route::get('/datapresensi', function () {
    return view('pdf.tugasharian');
})->name('pdfdatapresensi');

Route::get('/laporanpresensi', function () {
    return view('pdf.tugasharian');
})->name('pdflaporanpresensi');

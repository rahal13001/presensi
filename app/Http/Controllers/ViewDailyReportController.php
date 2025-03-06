<?php

namespace App\Http\Controllers;

use App\Models\Dailyreport;
use Illuminate\Http\Request;

class ViewDailyReportController extends Controller
{
    /**
     * Handle the incoming request.
     */

      use \Znck\Eloquent\Traits\BelongsToThrough;
    public function __invoke(Dailyreport $dailyreport)
    {        
        return view('laporanharian.laporanharian', compact('dailyreport'));
    }
}

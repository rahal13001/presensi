<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dailyreport extends Model
{
    protected $fillable = [
        'title',
        'description',
        'dokumentasi1',
        'dokumentasi2',
        'attendance_id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}

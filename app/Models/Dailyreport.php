<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dailyreport extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    protected $fillable = [
        'title',
        'description',
        'output',
        'note',
        'dokumentasi1',
        'dokumentasi2',
        'documentation3',
        'documentation4',
        'attendance_id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsToThrough(User::class, Attendance::class);
    }
}

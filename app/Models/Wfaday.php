<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wfaday extends Model
{
    protected $fillable = [
        'schedule_id',
        'day_name'
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}

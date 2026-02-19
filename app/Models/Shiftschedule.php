<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shiftschedule extends Model
{
    protected $fillable = [
        'shift_id',
        'office_id',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }


}

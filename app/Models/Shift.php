<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'position_id',
        'group',
        'end_time',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}

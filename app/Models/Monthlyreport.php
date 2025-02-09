<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monthlyreport extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'sign_date',
        'team_id',
        'user_sign',
        'team_sign'
    ];

    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

  
}

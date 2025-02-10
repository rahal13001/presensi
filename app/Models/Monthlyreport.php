<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monthlyreport extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'sign_date',
        'team_id',
        'user_sign',
        'team_sign'
    ];

    
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->dukman_leader)) {
                $model->dukman_leader = 'Hendrik Sombo, S.Pi., M.Si.';
            }
            if (empty($model->dukman_idnumber)) {
                $model->dukman_idnumber = '198201312005021001';
            }

            if (empty($model->user_id)) {
                $model->user_id = auth()->user()->id;
            }
        });
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function position(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Position::class, User::class);
    }

  
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    //
    protected $fillable = [
        'user_id',
        'team_name',
        'attendance_id',
        'date',
        'start_time',
        'end_time',
        'reason',
        'team_signature',
        'dukman_leader',
        'dukman_idnumber',
        'dukman_sign',
        'documentation1',
        'documentation2',
        'documentation3',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function position(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Position::class, User::class);
    }

}

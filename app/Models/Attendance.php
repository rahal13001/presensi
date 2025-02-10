<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Models\Leave;

class Attendance extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = [
        'user_id',
        'schedule_latitude',
        'schedule_longitude',
        'schedule_start_time',
        'schedule_end_time',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'start_time',
        'end_time',
        'is_leave',
        'start_accuracy',
        'end_accuracy',
        'not_present',
      
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyreports()
    {
        return $this->hasMany(Dailyreport::class);
    }

    public function position(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Position::class, User::class);
    }

    public function lateDuration()
    {

        if (is_null($this->start_time) && is_null($this->end_time)) {
            return "0 jam 0 menit";
        }
        else {
            $scheduleStartTime = Carbon::parse($this->schedule_start_time);
            $startTime = is_null($this->start_time)
                ? $scheduleStartTime->copy()->addHours(4)  // Apply 4-hour penalty for missing start_time
                : Carbon::parse($this->start_time);
        
            // Calculate lateness only if start_time is after schedule_start_time
            if ($startTime->greaterThan($scheduleStartTime)) {
                $lateMinutes = $scheduleStartTime->diffInMinutes($startTime);
                $lateHours = intdiv($lateMinutes, 60);
                $remainingMinutes = $lateMinutes % 60;
                return "{$lateHours} jam {$remainingMinutes} menit";
            }
        
            return "0 jam 0 menit";  // No lateness
        }
       
    }

    public function workDuration()
    {
        // Return "0 jam 0 menit" if both start_time and end_time are null
        if (!$this->start_time && !$this->end_time) {
            return "0 jam 0 menit";
        }
    
        $scheduleStartTime = Carbon::parse($this->schedule_start_time);
    
        // Handle missing start_time: Assume 4-hour penalty from scheduled start time
        if (!$this->start_time) {
            $startTime = $scheduleStartTime->addHours(4);
        } else {
            $startTime = Carbon::parse($this->start_time);
        }
    
        // Handle missing end_time: Assume 4-hour duration from start time
        if (!$this->end_time) {
            $endTime = $startTime->copy()->addHours(4);
        } else {
            $endTime = Carbon::parse($this->end_time);
    
            // Handle overnight shifts (end_time earlier than start_time)
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }
        }
    
        $duration = $startTime->diff($endTime);
        $hours = $duration->h + ($duration->d * 24);
        $minutes = $duration->i;
    
        return "{$hours} jam {$minutes} menit";
    }

}

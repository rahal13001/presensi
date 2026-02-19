<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReportV2 extends Model
{
    use HasFactory;

    protected $table = 'daily_reports_v2';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'report_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function scopeChecks(): HasMany
    {
        return $this->hasMany(DailyScopeCheck::class, 'daily_report_id');
    }

    public function otherWorks(): HasMany
    {
        return $this->hasMany(DailyOtherWork::class, 'daily_report_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DailyPhoto::class, 'daily_report_id');
    }
}

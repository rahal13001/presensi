<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'photo_path',
        'caption',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReportV2::class, 'daily_report_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_report_id',
        'daily_photo_id',
        'order',
    ];

    public function monthlyReport(): BelongsTo
    {
        return $this->belongsTo(MonthlyReportV2::class, 'monthly_report_id');
    }

    public function dailyPhoto(): BelongsTo
    {
        return $this->belongsTo(DailyPhoto::class);
    }
}

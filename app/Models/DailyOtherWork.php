<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyOtherWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'other_work_option_id',
        'description',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReportV2::class, 'daily_report_id');
    }

    public function otherWorkOption(): BelongsTo
    {
        return $this->belongsTo(OtherWorkOption::class);
    }
}

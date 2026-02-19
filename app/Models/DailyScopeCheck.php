<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyScopeCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'scope_id',
        'is_checked',
    ];

    protected function casts(): array
    {
        return [
            'is_checked' => 'boolean',
        ];
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReportV2::class, 'daily_report_id');
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyReportV2 extends Model
{
    use HasFactory;

    protected $table = 'monthly_reports_v2';

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'team_name',
        'team_leader_name',
        'city',
        'signed_date',
        'employee_sign',
        'employee_signed_at',
        'team_leader_id',
        'condition_status',
        'leader_notes',
        'leader_sign',
        'leader_signed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'employee_signed_at' => 'datetime',
            'leader_signed_at' => 'datetime',
            'signed_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MonthlyPhoto::class, 'monthly_report_id');
    }

    public function getScopeSummary(): array
    {
        $dailyReports = \App\Models\DailyReportV2::with(['scopeChecks.scope', 'otherWorks.otherWorkOption'])
            ->where('user_id', $this->user_id)
            ->whereMonth('report_date', $this->month)
            ->whereYear('report_date', $this->year)
            ->get();

        if ($dailyReports->isEmpty()) {
            return [
                'total_days' => 0,
                'scopes' => [],
                'others' => [],
            ];
        }

        $totalDays = $dailyReports->count();
        $scopeCounts = [];
        $otherWorkCounts = [];

        // Eager load scopes and other works to avoid N+1 queries during iteration if not already loaded
        foreach ($dailyReports as $report) {
            foreach ($report->scopeChecks as $check) {
                if ($check->is_checked && $check->scope) {
                    $scopeName = $check->scope->name;
                    $scopeCounts[$scopeName] = ($scopeCounts[$scopeName] ?? 0) + 1;
                }
            }
            
            foreach ($report->otherWorks as $work) {
                $workName = $work->otherWorkOption->name ?? 'Lainnya';
                $otherWorkCounts[$workName] = ($otherWorkCounts[$workName] ?? 0) + 1;
            }
        }

        return [
            'total_days' => $totalDays,
            'scopes' => $scopeCounts,
            'others' => $otherWorkCounts,
        ];
    }
}

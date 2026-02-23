<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'note',
        'typeofleave_id',
        'approved_by_name',
        'approved_at',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
            'attachment' => 'array',
        ];
    }

    // ─── Quota Helpers (called explicitly from action handlers) ──────

    /**
     * Helper to calculate actual leave days based on working days and holidays.
     */
    public static function calculateActualLeaveDays(int $typeofleaveId, $startDate, $endDate): int
    {
        $type = Typeofleave::find($typeofleaveId);
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if (!$type || !$type->has_quota || !$type->is_working_days_only) {
            return $start->diffInDays($end) + 1;
        }

        $days = 0;
        $period = \Carbon\CarbonPeriod::create($start, $end);
        $holidays = \App\Models\Holiday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->pluck('date')->toArray();

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }
            $days++;
        }

        return $days;
    }

    /**
     * Deduct quota for the given user/type/dates.
     */
    public static function deductQuota(int $userId, int $typeofleaveId, $startDate, $endDate): void
    {
        $type = Typeofleave::find($typeofleaveId);
        if (!$type || !$type->has_quota) return;

        $year = Carbon::parse($startDate)->year;
        $days = self::calculateActualLeaveDays($typeofleaveId, $startDate, $endDate);

        $quota = LeaveQuota::where('user_id', $userId)
            ->where('typeofleave_id', $typeofleaveId)
            ->where('year', $year)
            ->first();

        if ($quota) {
            $quota->increment('used_days', $days);
        }
    }

    /**
     * Refund quota for the given user/type/dates.
     */
    public static function refundQuota(int $userId, int $typeofleaveId, $startDate, $endDate): void
    {
        $type = Typeofleave::find($typeofleaveId);
        if (!$type || !$type->has_quota) return;

        $year = Carbon::parse($startDate)->year;
        $days = self::calculateActualLeaveDays($typeofleaveId, $startDate, $endDate);

        $quota = LeaveQuota::where('user_id', $userId)
            ->where('typeofleave_id', $typeofleaveId)
            ->where('year', $year)
            ->first();

        if ($quota) {
            $quota->decrement('used_days', $days);
            // Ensure used_days never goes below 0
            if ($quota->fresh()->used_days < 0) {
                $quota->update(['used_days' => 0]);
            }
        }
    }

    /**
     * Mark attendance records as leave.
     */
    public static function markAttendance(int $userId, int $typeofleaveId, $startDate, $endDate): void
    {
        $type = Typeofleave::find($typeofleaveId);
        $dates = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        
        $holidays = [];
        if ($type && $type->is_working_days_only) {
            $startFormatted = Carbon::parse($startDate)->format('Y-m-d');
            $endFormatted = Carbon::parse($endDate)->format('Y-m-d');
            $holidays = \App\Models\Holiday::whereBetween('date', [$startFormatted, $endFormatted])->pluck('date')->toArray();
        }

        foreach ($period as $date) {
            if ($type && $type->is_working_days_only) {
                if ($date->isWeekend() || in_array($date->format('Y-m-d'), $holidays)) {
                    continue;
                }
            }
            $dates[] = $date->format('Y-m-d');
        }

        if (count($dates) > 0) {
            Attendance::where('user_id', $userId)
                ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(start_time)'), $dates)
                ->update(['is_leave' => true]);
        }
    }

    /**
     * Unmark attendance records as leave.
     */
    public static function unmarkAttendance(int $userId, int $typeofleaveId, $startDate, $endDate): void
    {
        $type = Typeofleave::find($typeofleaveId);
        $dates = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        
        $holidays = [];
        if ($type && $type->is_working_days_only) {
            $startFormatted = Carbon::parse($startDate)->format('Y-m-d');
            $endFormatted = Carbon::parse($endDate)->format('Y-m-d');
            $holidays = \App\Models\Holiday::whereBetween('date', [$startFormatted, $endFormatted])->pluck('date')->toArray();
        }

        foreach ($period as $date) {
            if ($type && $type->is_working_days_only) {
                if ($date->isWeekend() || in_array($date->format('Y-m-d'), $holidays)) {
                    continue;
                }
            }
            $dates[] = $date->format('Y-m-d');
        }

        if (count($dates) > 0) {
            Attendance::where('user_id', $userId)
                ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(start_time)'), $dates)
                ->update(['is_leave' => false]);
        }
    }

    // ─── Relationships ──────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeofleave(): BelongsTo
    {
        return $this->belongsTo(Typeofleave::class);
    }

    // Helper to calculate total calendar days for the leave
    public function leaveDays(): int
    {
        if (!$this->start_date || !$this->end_date || !$this->typeofleave_id) {
            return 0;
        }

        return self::calculateActualLeaveDays($this->typeofleave_id, $this->start_date, $this->end_date);
    }
}

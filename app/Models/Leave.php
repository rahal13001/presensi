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
     * Deduct quota for the given user/type/dates.
     */
    public static function deductQuota(int $userId, int $typeofleaveId, $startDate, $endDate): void
    {
        $type = Typeofleave::find($typeofleaveId);
        if (!$type || !$type->has_quota) return;

        $year = Carbon::parse($startDate)->year;
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

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
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

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
    public static function markAttendance(int $userId, $startDate, $endDate): void
    {
        $dates = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        Attendance::where('user_id', $userId)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(start_time)'), $dates)
            ->update(['is_leave' => true]);
    }

    /**
     * Unmark attendance records as leave.
     */
    public static function unmarkAttendance(int $userId, $startDate, $endDate): void
    {
        $dates = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        Attendance::where('user_id', $userId)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(start_time)'), $dates)
            ->update(['is_leave' => false]);
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
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        return $start->diffInDays($end) + 1;
    }
}

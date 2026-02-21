<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveQuota extends Model
{
    protected $fillable = [
        'user_id',
        'typeofleave_id',
        'year',
        'total_days',
        'used_days',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeofleave(): BelongsTo
    {
        return $this->belongsTo(Typeofleave::class);
    }

    // Helper calculate remaining days
    public function getRemainingDaysAttribute(): int
    {
        return $this->total_days - $this->used_days;
    }
}

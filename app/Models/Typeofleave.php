<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Typeofleave extends Model
{
    protected $fillable = [
        'leaves_name',
        'has_quota',
        'default_quota_days',
        'requires_attachment',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'has_quota' => 'boolean',
            'requires_attachment' => 'boolean',
        ];
    }
}

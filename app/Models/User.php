<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position_id',
        'image',
        'idnumber'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

     // Encrypt the ID number before saving it to the database
    public function setIdNumberAttribute($value)
    {
        $this->attributes['idnumber'] = Crypt::encryptString($value);
    }
    
    // Decrypt the ID number when accessing it
    public function getIdNumberAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Check if it's a Laravel encrypted payload (base64 JSON starts with eyJ)
            if (str_starts_with($value, 'eyJ')) {
                return '[Data tidak dapat dibaca]';
            }
            // Otherwise, it might be an old unencrypted legacy NIK
            return $value;
        }
    }

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(Scope::class, 'user_scopes');
    }

    public function dailyReportsV2(): HasMany
    {
        return $this->hasMany(DailyReportV2::class);
    }

    public function monthlyReportsV2(): HasMany
    {
        return $this->hasMany(MonthlyReportV2::class);
    }

    // Teams the user belongs to as a MEMBER
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')->withTimestamps();
    }

    // Teams the user LEADS (is the kepala of)
    public function leadingTeams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}

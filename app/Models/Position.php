<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = ['position_name', 'description', 'group'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

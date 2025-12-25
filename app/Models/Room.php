<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['name', 'capacity', 'facilities'];

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
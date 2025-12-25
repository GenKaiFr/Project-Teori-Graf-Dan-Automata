<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Participant extends Model
{
    protected $fillable = ['name', 'email', 'role'];

    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'meeting_participants');
    }
}
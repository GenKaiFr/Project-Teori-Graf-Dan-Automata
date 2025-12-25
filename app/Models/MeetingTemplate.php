<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'default_participants',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'default_participants' => 'array',
        'is_active' => 'boolean'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'template_id');
    }
}
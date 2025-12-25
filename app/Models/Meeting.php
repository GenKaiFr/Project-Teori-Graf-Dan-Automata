<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Meeting extends Model
{
    protected $fillable = [
        'title', 'description', 'start_time', 'end_time', 'room_id', 'status', 'template_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'meeting_participants');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MeetingTemplate::class, 'template_id');
    }

    public function hasTimeConflict(Meeting $other): bool
    {
        return $this->start_time < $other->end_time && $this->end_time > $other->start_time;
    }

    public function hasRoomConflict(Meeting $other): bool
    {
        return $this->room_id === $other->room_id;
    }

    public function hasParticipantConflict(Meeting $other): bool
    {
        return $this->participants()->whereIn('participant_id', 
            $other->participants()->pluck('participant_id')
        )->exists();
    }

    public function isConflicting(Meeting $other): bool
    {
        $activeStatuses = ['DRAFT', 'SCHEDULED', 'ONGOING'];
        
        if (!in_array($this->status, $activeStatuses) || 
            !in_array($other->status, $activeStatuses)) {
            return false;
        }

        return $this->hasTimeConflict($other) && 
               ($this->hasRoomConflict($other) || $this->hasParticipantConflict($other));
    }
}
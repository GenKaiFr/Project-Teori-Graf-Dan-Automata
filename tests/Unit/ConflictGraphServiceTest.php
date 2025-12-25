<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ConflictGraphService;
use App\Models\Meeting;
use App\Models\Room;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ConflictGraphServiceTest extends TestCase
{
    public function test_detects_conflict_correctly()
    {
        $service = new ConflictGraphService();
        
        $room = new Room(['id' => 1, 'name' => 'Test Room']);
        
        $meeting1 = new Meeting([
            'id' => 1,
            'title' => 'Meeting 1',
            'start_time' => Carbon::parse('2024-01-01 09:00:00'),
            'end_time' => Carbon::parse('2024-01-01 10:00:00'),
            'room_id' => 1,
            'status' => 'SCHEDULED'
        ]);
        $meeting1->setRelation('room', $room);
        
        $meetings = new Collection([$meeting1]);
        $service->buildGraph($meetings);
        
        $newMeetingData = [
            'start_time' => '2024-01-01 09:30:00',
            'end_time' => '2024-01-01 10:30:00',
            'room_id' => 1
        ];
        
        $conflicts = $service->hasConflict($newMeetingData);
        
        $this->assertNotFalse($conflicts);
        $this->assertIsArray($conflicts);
        $this->assertCount(1, $conflicts);
    }

    public function test_no_conflict_different_rooms()
    {
        $service = new ConflictGraphService();
        
        $room1 = new Room(['id' => 1, 'name' => 'Room 1']);
        $room2 = new Room(['id' => 2, 'name' => 'Room 2']);
        
        $meeting1 = new Meeting([
            'id' => 1,
            'start_time' => Carbon::parse('2024-01-01 09:00:00'),
            'end_time' => Carbon::parse('2024-01-01 10:00:00'),
            'room_id' => 1,
            'status' => 'SCHEDULED'
        ]);
        $meeting1->setRelation('room', $room1);
        
        $meetings = new Collection([$meeting1]);
        $service->buildGraph($meetings);
        
        $newMeetingData = [
            'start_time' => '2024-01-01 09:30:00',
            'end_time' => '2024-01-01 10:30:00',
            'room_id' => 2
        ];
        
        $conflicts = $service->hasConflict($newMeetingData);
        
        $this->assertFalse($conflicts);
    }
}
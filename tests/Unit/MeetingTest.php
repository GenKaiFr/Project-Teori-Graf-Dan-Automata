<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Meeting;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_meeting_has_time_conflict()
    {
        $meeting1 = new Meeting([
            'start_time' => Carbon::parse('2024-01-01 09:00:00'),
            'end_time' => Carbon::parse('2024-01-01 10:00:00'),
            'status' => 'SCHEDULED'
        ]);

        $meeting2 = new Meeting([
            'start_time' => Carbon::parse('2024-01-01 09:30:00'),
            'end_time' => Carbon::parse('2024-01-01 10:30:00'),
            'status' => 'SCHEDULED'
        ]);

        $this->assertTrue($meeting1->hasTimeConflict($meeting2));
    }

    public function test_meeting_no_time_conflict()
    {
        $meeting1 = new Meeting([
            'start_time' => Carbon::parse('2024-01-01 09:00:00'),
            'end_time' => Carbon::parse('2024-01-01 10:00:00'),
            'status' => 'SCHEDULED'
        ]);

        $meeting2 = new Meeting([
            'start_time' => Carbon::parse('2024-01-01 10:00:00'),
            'end_time' => Carbon::parse('2024-01-01 11:00:00'),
            'status' => 'SCHEDULED'
        ]);

        $this->assertFalse($meeting1->hasTimeConflict($meeting2));
    }

    public function test_meeting_has_room_conflict()
    {
        $meeting1 = new Meeting(['room_id' => 1]);
        $meeting2 = new Meeting(['room_id' => 1]);

        $this->assertTrue($meeting1->hasRoomConflict($meeting2));
    }

    public function test_meeting_no_room_conflict()
    {
        $meeting1 = new Meeting(['room_id' => 1]);
        $meeting2 = new Meeting(['room_id' => 2]);

        $this->assertFalse($meeting1->hasRoomConflict($meeting2));
    }
}
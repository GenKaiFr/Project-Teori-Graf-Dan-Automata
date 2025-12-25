<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;

class MeetingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_view_meetings_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get('/meetings');
        
        $response->assertStatus(200);
        $response->assertViewIs('meetings.index');
    }

    public function test_manager_can_create_meeting()
    {
        $user = User::factory()->create(['role' => 'manager']);
        $room = Room::first();
        
        $response = $this->actingAs($user)->post('/meetings', [
            'title' => 'Test Meeting',
            'description' => 'Test Description',
            'start_time' => '2024-12-15 09:00:00',
            'end_time' => '2024-12-15 10:00:00',
            'room_id' => $room->id,
            'participants' => []
        ]);
        
        $response->assertRedirect('/meetings');
        $this->assertDatabaseHas('meetings', [
            'title' => 'Test Meeting'
        ]);
    }

    public function test_user_cannot_create_meeting()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get('/meetings/create');
        
        $response->assertStatus(403);
    }

    public function test_conflict_detection_prevents_meeting_creation()
    {
        $user = User::factory()->create(['role' => 'manager']);
        $room = Room::first();
        
        // Create first meeting
        Meeting::create([
            'title' => 'Existing Meeting',
            'start_time' => '2024-12-15 09:00:00',
            'end_time' => '2024-12-15 10:00:00',
            'room_id' => $room->id,
            'status' => 'SCHEDULED'
        ]);
        
        // Try to create conflicting meeting
        $response = $this->actingAs($user)->post('/meetings', [
            'title' => 'Conflicting Meeting',
            'start_time' => '2024-12-15 09:30:00',
            'end_time' => '2024-12-15 10:30:00',
            'room_id' => $room->id,
            'participants' => []
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_dashboard_shows_statistics()
    {
        $user = User::factory()->create(['role' => 'manager']);
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertViewIs('meetings.dashboard');
        $response->assertViewHas('stats');
    }
}
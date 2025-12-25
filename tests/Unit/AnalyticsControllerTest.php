<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AnalyticsController;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with manager role
        $this->user = User::factory()->create([
            'role' => 'manager'
        ]);
        
        $this->controller = new AnalyticsController();
        
        // Create test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create rooms
        Room::create(['name' => 'Meeting Room A', 'capacity' => 10]);
        Room::create(['name' => 'Meeting Room B', 'capacity' => 8]);
        
        // Create participants
        Participant::create(['name' => 'John Doe', 'email' => 'john@test.com']);
        Participant::create(['name' => 'Jane Smith', 'email' => 'jane@test.com']);
        
        // Create test meetings
        $room = Room::first();
        $participants = Participant::all();
        
        for ($i = 0; $i < 5; $i++) {
            $meeting = Meeting::create([
                'title' => "Test Meeting $i",
                'description' => "Test Description $i",
                'start_time' => now()->subDays($i)->setTime(9, 0),
                'end_time' => now()->subDays($i)->setTime(10, 0),
                'room_id' => $room->id,
                'status' => 'COMPLETED'
            ]);
            
            $meeting->participants()->attach($participants->pluck('id'));
        }
    }

    public function test_controller_can_be_instantiated()
    {
        $this->assertInstanceOf(AnalyticsController::class, $this->controller);
    }

    public function test_analytics_page_requires_authentication()
    {
        $response = $this->get(route('analytics.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_analytics_page_requires_manager_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get(route('analytics.index'));
        $response->assertStatus(403);
    }

    public function test_manager_can_access_analytics_page()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.index'));
        $response->assertStatus(200);
        $response->assertViewIs('analytics.index');
    }

    public function test_analytics_api_returns_json()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.api'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'analytics',
            'predictions',
            'insights',
            'trends',
            'performance'
        ]);
    }

    public function test_analytics_data_contains_required_fields()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.api'));
        $data = $response->json();
        
        // Test analytics structure
        $this->assertArrayHasKey('efficiency', $data['analytics']);
        $this->assertArrayHasKey('growth', $data['analytics']);
        $this->assertArrayHasKey('costs', $data['analytics']);
        
        // Test predictions structure
        $this->assertArrayHasKey('next_month_meetings', $data['predictions']);
        $this->assertArrayHasKey('room_demand', $data['predictions']);
        
        // Test insights structure
        $this->assertArrayHasKey('productivity_score', $data['insights']);
        $this->assertArrayHasKey('meeting_quality_index', $data['insights']);
        $this->assertArrayHasKey('recommendations', $data['insights']);
    }

    public function test_productivity_score_is_numeric()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.api'));
        $data = $response->json();
        
        $this->assertIsNumeric($data['insights']['productivity_score']);
        $this->assertGreaterThanOrEqual(0, $data['insights']['productivity_score']);
        $this->assertLessThanOrEqual(100, $data['insights']['productivity_score']);
    }

    public function test_recommendations_are_array()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.api'));
        $data = $response->json();
        
        $this->assertIsArray($data['insights']['recommendations']);
        
        if (!empty($data['insights']['recommendations'])) {
            $recommendation = $data['insights']['recommendations'][0];
            $this->assertArrayHasKey('type', $recommendation);
            $this->assertArrayHasKey('priority', $recommendation);
            $this->assertArrayHasKey('title', $recommendation);
            $this->assertArrayHasKey('description', $recommendation);
            $this->assertArrayHasKey('action', $recommendation);
        }
    }

    public function test_export_pdf_works()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.exportPdf'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_excel_works()
    {
        $response = $this->actingAs($this->user)->get(route('analytics.exportExcel'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_export_requires_manager_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get(route('analytics.exportPdf'));
        $response->assertStatus(403);
        
        $response = $this->actingAs($user)->get(route('analytics.exportExcel'));
        $response->assertStatus(403);
    }
}
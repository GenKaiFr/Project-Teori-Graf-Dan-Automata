<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;
use Carbon\Carbon;

class SampleMeetingsSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all();
        $participants = Participant::all();
        
        if ($rooms->isEmpty() || $participants->isEmpty()) {
            $this->command->info('Please run MeetingSystemSeeder first to create rooms and participants.');
            return;
        }

        $statuses = ['DRAFT', 'SCHEDULED', 'ONGOING', 'COMPLETED', 'CANCELLED'];
        $meetingTitles = [
            'Daily Standup',
            'Sprint Planning',
            'Code Review',
            'Client Meeting',
            'Team Retrospective',
            'Product Demo',
            'Architecture Discussion',
            'Budget Review',
            'Marketing Strategy',
            'Sales Update',
            'Training Session',
            'Project Kickoff',
            'Status Update',
            'Technical Discussion',
            'User Feedback Review'
        ];

        // Generate meetings for the last 3 months
        for ($i = 0; $i < 150; $i++) {
            $startDate = Carbon::now()->subDays(rand(1, 90));
            $startHour = rand(8, 17); // Business hours
            $duration = [30, 60, 90, 120][rand(0, 3)]; // Random duration
            
            $startTime = $startDate->copy()->setHour($startHour)->setMinute([0, 30][rand(0, 1)]);
            $endTime = $startTime->copy()->addMinutes($duration);
            
            $meeting = Meeting::create([
                'title' => $meetingTitles[rand(0, count($meetingTitles) - 1)],
                'description' => 'Sample meeting description for testing statistics.',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'room_id' => $rooms->random()->id,
                'status' => $statuses[rand(0, count($statuses) - 1)]
            ]);

            // Attach random participants (1-5 participants per meeting)
            $randomParticipants = $participants->random(rand(1, min(5, $participants->count())));
            $meeting->participants()->attach($randomParticipants->pluck('id'));
        }

        $this->command->info('Sample meetings created successfully!');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $stats = $this->getBasicStats();
        $monthlyData = $this->getMonthlyMeetingData();
        $roomUtilization = $this->getRoomUtilizationData();
        $statusDistribution = $this->getStatusDistribution();
        $participantEngagement = $this->getParticipantEngagement();
        $timePatterns = $this->getTimePatterns();

        return view('statistics.index', compact(
            'stats', 
            'monthlyData', 
            'roomUtilization', 
            'statusDistribution',
            'participantEngagement',
            'timePatterns'
        ));
    }

    private function getBasicStats()
    {
        $totalMeetings = Meeting::count();
        
        // Calculate total duration using PHP instead of SQL for SQLite compatibility
        $meetings = Meeting::select('start_time', 'end_time')->get();
        $totalDuration = 0;
        foreach ($meetings as $meeting) {
            $totalDuration += Carbon::parse($meeting->start_time)->diffInMinutes(Carbon::parse($meeting->end_time));
        }
        
        $avgDuration = $totalMeetings > 0 ? round($totalDuration / $totalMeetings) : 0;
        $totalParticipants = DB::table('meeting_participants')->distinct('participant_id')->count();
        
        return [
            'total_meetings' => $totalMeetings,
            'total_duration_hours' => round($totalDuration / 60, 1),
            'avg_duration_minutes' => $avgDuration,
            'total_participants' => $totalParticipants,
            'meetings_this_month' => Meeting::whereMonth('start_time', now()->month)->count(),
            'meetings_last_month' => Meeting::whereMonth('start_time', now()->subMonth()->month)->count()
        ];
    }

    private function getMonthlyMeetingData()
    {
        $data = Meeting::selectRaw('strftime("%Y", start_time) as year, strftime("%m", start_time) as month, COUNT(*) as count')
            ->where('start_time', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $months = [];
        $counts = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            $found = $data->first(function($item) use ($date) {
                return $item->year == $date->year && intval($item->month) == $date->month;
            });
            
            $months[] = $date->format('M Y');
            $counts[] = $found ? $found->count : 0;
        }

        return [
            'months' => $months,
            'counts' => $counts
        ];
    }

    private function getRoomUtilizationData()
    {
        $rooms = Room::withCount(['meetings' => function($query) {
            $query->where('start_time', '>=', now()->subMonth());
        }])->get();

        $roomNames = $rooms->pluck('name')->toArray();
        $meetingCounts = $rooms->pluck('meetings_count')->toArray();

        // Calculate utilization percentage (assuming 8 hours per day, 22 working days per month)
        $totalAvailableHours = 8 * 22; // 176 hours per month
        $utilization = [];

        foreach ($rooms as $room) {
            $roomMeetings = Meeting::where('room_id', $room->id)
                ->where('start_time', '>=', now()->subMonth())
                ->select('start_time', 'end_time')
                ->get();
            
            $totalMeetingMinutes = 0;
            foreach ($roomMeetings as $meeting) {
                $totalMeetingMinutes += Carbon::parse($meeting->start_time)->diffInMinutes(Carbon::parse($meeting->end_time));
            }
            
            $utilizationPercent = ($totalMeetingMinutes / ($totalAvailableHours * 60)) * 100;
            $utilization[] = round($utilizationPercent, 1);
        }

        return [
            'room_names' => $roomNames,
            'meeting_counts' => $meetingCounts,
            'utilization_percent' => $utilization
        ];
    }

    private function getStatusDistribution()
    {
        $distribution = Meeting::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $statuses = $distribution->pluck('status')->toArray();
        $counts = $distribution->pluck('count')->toArray();

        return [
            'statuses' => $statuses,
            'counts' => $counts
        ];
    }

    private function getParticipantEngagement()
    {
        $engagement = DB::table('meeting_participants')
            ->join('participants', 'meeting_participants.participant_id', '=', 'participants.id')
            ->join('meetings', 'meeting_participants.meeting_id', '=', 'meetings.id')
            ->where('meetings.start_time', '>=', now()->subMonth())
            ->selectRaw('participants.name, COUNT(*) as meeting_count')
            ->groupBy('participants.id', 'participants.name')
            ->orderByDesc('meeting_count')
            ->limit(10)
            ->get();

        return [
            'participant_names' => $engagement->pluck('name')->toArray(),
            'meeting_counts' => $engagement->pluck('meeting_count')->toArray()
        ];
    }

    private function getTimePatterns()
    {
        $hourlyData = Meeting::selectRaw('strftime("%H", start_time) as hour, COUNT(*) as count')
            ->where('start_time', '>=', now()->subMonth())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = range(0, 23);
        $counts = array_fill(0, 24, 0);

        foreach ($hourlyData as $data) {
            $counts[intval($data->hour)] = $data->count;
        }

        $dayOfWeekData = Meeting::selectRaw('strftime("%w", start_time) as day_of_week, COUNT(*) as count')
            ->where('start_time', '>=', now()->subMonth())
            ->groupBy('day_of_week')
            ->get();

        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayCounts = array_fill(0, 7, 0);

        foreach ($dayOfWeekData as $data) {
            $dayCounts[intval($data->day_of_week)] = $data->count;
        }

        return [
            'hourly' => [
                'hours' => $hours,
                'counts' => $counts
            ],
            'daily' => [
                'days' => $dayNames,
                'counts' => $dayCounts
            ]
        ];
    }

    public function api()
    {
        return response()->json([
            'basic_stats' => $this->getBasicStats(),
            'monthly_data' => $this->getMonthlyMeetingData(),
            'room_utilization' => $this->getRoomUtilizationData(),
            'status_distribution' => $this->getStatusDistribution(),
            'participant_engagement' => $this->getParticipantEngagement(),
            'time_patterns' => $this->getTimePatterns()
        ]);
    }
}
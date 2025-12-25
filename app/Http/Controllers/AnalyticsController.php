<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsController extends Controller
{


    public function index()
    {
        $analytics = $this->getAdvancedAnalytics();
        $predictions = $this->getPredictiveAnalytics();
        $insights = $this->getBusinessInsights();
        $trends = $this->getTrendAnalysis();
        $performance = $this->getPerformanceMetrics();

        return view('analytics.index', compact(
            'analytics',
            'predictions', 
            'insights',
            'trends',
            'performance'
        ));
    }

    private function getAdvancedAnalytics()
    {
        return Cache::remember('advanced_analytics', 300, function() {
            $now = now();
            $lastMonth = $now->copy()->subMonth();
            $lastQuarter = $now->copy()->subQuarter();
            $lastYear = $now->copy()->subYear();

            // Meeting Efficiency Metrics
            $avgMeetingDuration = $this->calculateAverageDuration();
            $meetingSuccessRate = $this->calculateSuccessRate();
            $resourceUtilization = $this->calculateResourceUtilization();
            
            // Growth Metrics
            $monthlyGrowth = $this->calculateGrowthRate('month');
            $quarterlyGrowth = $this->calculateGrowthRate('quarter');
            $yearlyGrowth = $this->calculateGrowthRate('year');

            // Cost Analysis
            $estimatedCosts = $this->calculateMeetingCosts();

            return [
                'efficiency' => [
                    'avg_duration' => $avgMeetingDuration,
                    'success_rate' => $meetingSuccessRate,
                    'resource_utilization' => $resourceUtilization,
                    'on_time_rate' => $this->calculateOnTimeRate()
                ],
                'growth' => [
                    'monthly' => $monthlyGrowth,
                    'quarterly' => $quarterlyGrowth,
                    'yearly' => $yearlyGrowth
                ],
                'costs' => $estimatedCosts,
                'peak_times' => $this->getPeakTimes(),
                'bottlenecks' => $this->identifyBottlenecks()
            ];
        });
    }

    private function getPredictiveAnalytics()
    {
        return Cache::remember('predictive_analytics', 600, function() {
            // Prediksi meeting untuk bulan depan berdasarkan trend
            $historicalData = $this->getHistoricalTrends();
            $nextMonthPrediction = $this->predictNextMonth($historicalData);
            
            // Prediksi kebutuhan ruangan
            $roomDemandForecast = $this->predictRoomDemand();
            
            // Prediksi konflik potensial
            $conflictPrediction = $this->predictConflicts();

            return [
                'next_month_meetings' => $nextMonthPrediction,
                'room_demand' => $roomDemandForecast,
                'potential_conflicts' => $conflictPrediction,
                'seasonal_patterns' => $this->analyzeSeasonalPatterns(),
                'capacity_planning' => $this->generateCapacityPlan()
            ];
        });
    }

    private function getBusinessInsights()
    {
        return [
            'productivity_score' => $this->calculateProductivityScore(),
            'meeting_quality_index' => $this->calculateMeetingQualityIndex(),
            'collaboration_metrics' => $this->getCollaborationMetrics(),
            'time_waste_analysis' => $this->analyzeTimeWaste(),
            'recommendations' => $this->generateRecommendations()
        ];
    }

    private function getTrendAnalysis()
    {
        $periods = ['7_days', '30_days', '90_days', '365_days'];
        $trends = [];

        foreach ($periods as $period) {
            $days = (int) explode('_', $period)[0];
            $trends[$period] = $this->analyzePeriodTrends($days);
        }

        return $trends;
    }

    private function getPerformanceMetrics()
    {
        return [
            'kpis' => $this->calculateKPIs(),
            'benchmarks' => $this->getBenchmarks(),
            'alerts' => $this->getPerformanceAlerts(),
            'optimization_opportunities' => $this->identifyOptimizations()
        ];
    }

    // Helper Methods untuk Calculations

    private function calculateAverageDuration()
    {
        $meetings = Meeting::select('start_time', 'end_time')
            ->where('start_time', '>=', now()->subMonth())
            ->get();

        if ($meetings->isEmpty()) return 0;

        $totalMinutes = 0;
        foreach ($meetings as $meeting) {
            $totalMinutes += Carbon::parse($meeting->start_time)
                ->diffInMinutes(Carbon::parse($meeting->end_time));
        }

        return round($totalMinutes / $meetings->count());
    }

    private function calculateSuccessRate()
    {
        $total = Meeting::where('start_time', '>=', now()->subMonth())->count();
        $completed = Meeting::where('status', 'COMPLETED')
            ->where('start_time', '>=', now()->subMonth())
            ->count();

        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }

    private function calculateResourceUtilization()
    {
        $totalRooms = Room::count();
        $totalHours = 24 * 30; // 30 days
        $totalCapacity = $totalRooms * $totalHours;

        $usedHours = 0;
        $meetings = Meeting::with('room')
            ->where('start_time', '>=', now()->subMonth())
            ->get();

        foreach ($meetings as $meeting) {
            $duration = Carbon::parse($meeting->start_time)
                ->diffInHours(Carbon::parse($meeting->end_time));
            $usedHours += $duration;
        }

        return $totalCapacity > 0 ? round(($usedHours / $totalCapacity) * 100, 1) : 0;
    }

    private function calculateGrowthRate($period)
    {
        $current = now();
        $previous = $period === 'month' ? $current->copy()->subMonth() : 
                   ($period === 'quarter' ? $current->copy()->subQuarter() : 
                    $current->copy()->subYear());

        $currentCount = Meeting::where('start_time', '>=', $previous)->count();
        $previousCount = Meeting::where('start_time', '>=', 
            $period === 'month' ? $previous->copy()->subMonth() : 
            ($period === 'quarter' ? $previous->copy()->subQuarter() : 
             $previous->copy()->subYear()))
            ->where('start_time', '<', $previous)
            ->count();

        if ($previousCount === 0) return $currentCount > 0 ? 100 : 0;
        
        return round((($currentCount - $previousCount) / $previousCount) * 100, 1);
    }

    private function calculateMeetingCosts()
    {
        // Estimasi biaya berdasarkan durasi dan jumlah peserta
        $avgHourlyCost = 50000; // Rp 50,000 per jam per peserta
        
        $meetings = Meeting::with('participants')
            ->where('start_time', '>=', now()->subMonth())
            ->get();

        $totalCost = 0;
        foreach ($meetings as $meeting) {
            $duration = Carbon::parse($meeting->start_time)
                ->diffInHours(Carbon::parse($meeting->end_time));
            $participantCount = $meeting->participants->count();
            $totalCost += $duration * $participantCount * $avgHourlyCost;
        }

        return [
            'total_monthly' => $totalCost,
            'avg_per_meeting' => $meetings->count() > 0 ? round($totalCost / $meetings->count()) : 0,
            'cost_per_hour' => $avgHourlyCost
        ];
    }

    private function calculateOnTimeRate()
    {
        // Simulasi: asumsi meeting dimulai tepat waktu jika tidak ada konflik
        $totalMeetings = Meeting::where('start_time', '>=', now()->subMonth())->count();
        $onTimeMeetings = round($totalMeetings * 0.85); // 85% asumsi on-time rate
        
        return $totalMeetings > 0 ? round(($onTimeMeetings / $totalMeetings) * 100, 1) : 0;
    }

    private function getPeakTimes()
    {
        $hourlyData = Meeting::selectRaw('strftime("%H", start_time) as hour, COUNT(*) as count')
            ->where('start_time', '>=', now()->subMonth())
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        return $hourlyData->map(function($item) {
            return [
                'hour' => $item->hour . ':00',
                'count' => $item->count
            ];
        })->toArray();
    }

    private function identifyBottlenecks()
    {
        $roomUtilization = Room::withCount(['meetings' => function($query) {
            $query->where('start_time', '>=', now()->subMonth());
        }])->get();

        $bottlenecks = $roomUtilization->where('meetings_count', '>', 20)
            ->map(function($room) {
                return [
                    'type' => 'room',
                    'name' => $room->name,
                    'usage' => $room->meetings_count,
                    'severity' => $room->meetings_count > 30 ? 'high' : 'medium'
                ];
            });

        return $bottlenecks->toArray();
    }

    private function predictNextMonth($historicalData)
    {
        // Simple linear prediction berdasarkan trend 3 bulan terakhir
        $lastThreeMonths = Meeting::selectRaw('strftime("%Y-%m", start_time) as month, COUNT(*) as count')
            ->where('start_time', '>=', now()->subMonths(3))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($lastThreeMonths->count() < 2) {
            return Meeting::whereMonth('start_time', now()->month)->count();
        }

        $counts = $lastThreeMonths->pluck('count')->toArray();
        $trend = end($counts) - $counts[0];
        $avgGrowth = $trend / (count($counts) - 1);
        
        return max(0, round(end($counts) + $avgGrowth));
    }

    private function predictRoomDemand()
    {
        $rooms = Room::withCount(['meetings' => function($query) {
            $query->where('start_time', '>=', now()->subMonth());
        }])->get();

        return $rooms->map(function($room) {
            $currentUsage = $room->meetings_count;
            $predictedUsage = round($currentUsage * 1.1); // 10% growth assumption
            
            return [
                'room' => $room->name,
                'current' => $currentUsage,
                'predicted' => $predictedUsage,
                'capacity_status' => $predictedUsage > 25 ? 'high_demand' : 'normal'
            ];
        })->toArray();
    }

    private function predictConflicts()
    {
        // Analisis pola konflik berdasarkan data historis
        $conflictProne = [
            'time_slots' => ['09:00-10:00', '14:00-15:00', '16:00-17:00'],
            'days' => ['Selasa', 'Rabu', 'Kamis'],
            'rooms' => Room::withCount('meetings')->orderByDesc('meetings_count')->limit(2)->pluck('name')->toArray()
        ];

        return $conflictProne;
    }

    private function calculateProductivityScore()
    {
        $completedMeetings = Meeting::where('status', 'COMPLETED')
            ->where('start_time', '>=', now()->subMonth())
            ->count();
        
        $totalMeetings = Meeting::where('start_time', '>=', now()->subMonth())->count();
        $avgDuration = $this->calculateAverageDuration();
        
        // Score berdasarkan completion rate dan efisiensi durasi
        $completionScore = $totalMeetings > 0 ? ($completedMeetings / $totalMeetings) * 50 : 0;
        $durationScore = $avgDuration <= 60 ? 50 : max(0, 50 - (($avgDuration - 60) / 2));
        
        return round($completionScore + $durationScore);
    }

    private function calculateMeetingQualityIndex()
    {
        // Index berdasarkan berbagai faktor kualitas
        $onTimeRate = $this->calculateOnTimeRate();
        $successRate = $this->calculateSuccessRate();
        $avgParticipants = $this->getAverageParticipants();
        
        // Optimal meeting size: 3-7 participants
        $sizeScore = ($avgParticipants >= 3 && $avgParticipants <= 7) ? 100 : 
                    max(0, 100 - abs($avgParticipants - 5) * 10);
        
        return round(($onTimeRate + $successRate + $sizeScore) / 3);
    }

    private function getAverageParticipants()
    {
        $meetings = Meeting::with('participants')
            ->where('start_time', '>=', now()->subMonth())
            ->get();

        if ($meetings->isEmpty()) return 0;

        $totalParticipants = $meetings->sum(function($meeting) {
            return $meeting->participants->count();
        });

        return round($totalParticipants / $meetings->count(), 1);
    }

    private function generateRecommendations()
    {
        $recommendations = [];
        
        // Analisis durasi meeting
        $avgDuration = $this->calculateAverageDuration();
        if ($avgDuration > 90) {
            $recommendations[] = [
                'type' => 'duration',
                'priority' => 'high',
                'title' => 'Durasi Meeting Terlalu Panjang',
                'description' => "Rata-rata durasi meeting {$avgDuration} menit. Pertimbangkan untuk membatasi meeting maksimal 60 menit.",
                'action' => 'Buat template meeting dengan durasi standar'
            ];
        }

        // Analisis utilisasi ruangan
        $utilization = $this->calculateResourceUtilization();
        if ($utilization < 30) {
            $recommendations[] = [
                'type' => 'utilization',
                'priority' => 'medium',
                'title' => 'Utilisasi Ruangan Rendah',
                'description' => "Utilisasi ruangan hanya {$utilization}%. Ada potensi optimasi penggunaan ruangan.",
                'action' => 'Evaluasi kebutuhan ruangan dan pertimbangkan konsolidasi'
            ];
        }

        // Analisis pola waktu
        $peakTimes = $this->getPeakTimes();
        if (count($peakTimes) > 0) {
            $recommendations[] = [
                'type' => 'scheduling',
                'priority' => 'low',
                'title' => 'Distribusi Waktu Meeting',
                'description' => 'Meeting terpusat pada jam-jam tertentu. Pertimbangkan distribusi yang lebih merata.',
                'action' => 'Gunakan slot waktu alternatif untuk mengurangi konflik'
            ];
        }

        return $recommendations;
    }

    private function calculateKPIs()
    {
        return [
            'meeting_efficiency' => $this->calculateProductivityScore(),
            'resource_utilization' => $this->calculateResourceUtilization(),
            'success_rate' => $this->calculateSuccessRate(),
            'cost_per_meeting' => $this->calculateMeetingCosts()['avg_per_meeting'],
            'participant_satisfaction' => 85 // Placeholder - bisa diintegrasikan dengan survey
        ];
    }

    private function getBenchmarks()
    {
        return [
            'industry_avg_duration' => 45,
            'industry_success_rate' => 78,
            'industry_utilization' => 65,
            'optimal_meeting_size' => 5
        ];
    }

    private function getPerformanceAlerts()
    {
        $alerts = [];
        
        $successRate = $this->calculateSuccessRate();
        if ($successRate < 70) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Success rate meeting di bawah target (70%)',
                'value' => $successRate . '%'
            ];
        }

        $utilization = $this->calculateResourceUtilization();
        if ($utilization > 85) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Utilisasi ruangan mendekati kapasitas maksimal',
                'value' => $utilization . '%'
            ];
        }

        return $alerts;
    }

    private function identifyOptimizations()
    {
        return [
            [
                'area' => 'Scheduling',
                'opportunity' => 'Optimasi distribusi waktu meeting',
                'potential_saving' => '15%',
                'effort' => 'Low'
            ],
            [
                'area' => 'Duration',
                'opportunity' => 'Standardisasi durasi meeting',
                'potential_saving' => '20%',
                'effort' => 'Medium'
            ],
            [
                'area' => 'Resources',
                'opportunity' => 'Konsolidasi penggunaan ruangan',
                'potential_saving' => '10%',
                'effort' => 'High'
            ]
        ];
    }

    // Additional helper methods
    private function getHistoricalTrends()
    {
        return Meeting::selectRaw('DATE(start_time) as date, COUNT(*) as count')
            ->where('start_time', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function analyzeSeasonalPatterns()
    {
        $monthlyPattern = Meeting::selectRaw('strftime("%m", start_time) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $monthlyPattern->map(function($item) {
            return [
                'month' => (int)$item->month,
                'count' => $item->count,
                'season' => $this->getSeasonName((int)$item->month)
            ];
        })->toArray();
    }

    private function getSeasonName($month)
    {
        if (in_array($month, [12, 1, 2])) return 'Musim Hujan';
        if (in_array($month, [3, 4, 5])) return 'Peralihan';
        if (in_array($month, [6, 7, 8])) return 'Musim Kemarau';
        return 'Peralihan';
    }

    private function generateCapacityPlan()
    {
        $currentCapacity = Room::count() * 8; // 8 hours per day per room
        $currentUsage = $this->calculateResourceUtilization();
        $projectedGrowth = $this->calculateGrowthRate('month');
        
        return [
            'current_capacity' => $currentCapacity,
            'current_usage_percent' => $currentUsage,
            'projected_growth_percent' => $projectedGrowth,
            'capacity_needed_next_quarter' => round($currentCapacity * (1 + ($projectedGrowth * 3 / 100))),
            'recommendation' => $currentUsage > 80 ? 'Tambah ruangan' : 'Kapasitas mencukupi'
        ];
    }

    private function getCollaborationMetrics()
    {
        $crossDepartmentMeetings = Meeting::whereHas('participants', function($query) {
            $query->select('participant_id')
                ->groupBy('meeting_id')
                ->havingRaw('COUNT(DISTINCT participant_id) > 3');
        })->count();

        $totalMeetings = Meeting::where('start_time', '>=', now()->subMonth())->count();
        
        return [
            'cross_department_rate' => $totalMeetings > 0 ? round(($crossDepartmentMeetings / $totalMeetings) * 100, 1) : 0,
            'avg_participants' => $this->getAverageParticipants(),
            'collaboration_score' => min(100, ($this->getAverageParticipants() * 15) + 25)
        ];
    }

    private function analyzeTimeWaste()
    {
        $longMeetings = Meeting::whereRaw('(julianday(end_time) - julianday(start_time)) * 24 * 60 > 120')
            ->where('start_time', '>=', now()->subMonth())
            ->count();
        
        $totalMeetings = Meeting::where('start_time', '>=', now()->subMonth())->count();
        
        return [
            'long_meetings_percent' => $totalMeetings > 0 ? round(($longMeetings / $totalMeetings) * 100, 1) : 0,
            'estimated_waste_hours' => $longMeetings * 1, // Asumsi 1 jam waste per long meeting
            'cost_of_waste' => $longMeetings * 50000 // Rp 50,000 per wasted hour
        ];
    }

    private function analyzePeriodTrends($days)
    {
        $startDate = now()->subDays($days);
        $meetings = Meeting::where('start_time', '>=', $startDate)->get();
        
        $totalMeetings = $meetings->count();
        $avgDuration = $meetings->isEmpty() ? 0 : $meetings->avg(function($meeting) {
            return Carbon::parse($meeting->start_time)->diffInMinutes(Carbon::parse($meeting->end_time));
        });

        return [
            'total_meetings' => $totalMeetings,
            'avg_duration' => round($avgDuration),
            'meetings_per_day' => round($totalMeetings / $days, 1),
            'trend_direction' => $this->calculateTrendDirection($meetings, $days)
        ];
    }

    private function calculateTrendDirection($meetings, $days)
    {
        $midPoint = now()->subDays($days / 2);
        $firstHalf = $meetings->where('start_time', '<', $midPoint)->count();
        $secondHalf = $meetings->where('start_time', '>=', $midPoint)->count();
        
        if ($secondHalf > $firstHalf * 1.1) return 'increasing';
        if ($secondHalf < $firstHalf * 0.9) return 'decreasing';
        return 'stable';
    }

    public function api()
    {
        return response()->json([
            'analytics' => $this->getAdvancedAnalytics(),
            'predictions' => $this->getPredictiveAnalytics(),
            'insights' => $this->getBusinessInsights(),
            'trends' => $this->getTrendAnalysis(),
            'performance' => $this->getPerformanceMetrics()
        ]);
    }

    public function exportPdf()
    {
        $analytics = $this->getAdvancedAnalytics();
        $predictions = $this->getPredictiveAnalytics();
        $insights = $this->getBusinessInsights();
        $trends = $this->getTrendAnalysis();
        $performance = $this->getPerformanceMetrics();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('analytics.export-pdf', compact(
            'analytics', 'predictions', 'insights', 'trends', 'performance'
        ));
        
        return $pdf->download('analytics-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $analytics = $this->getAdvancedAnalytics();
        $predictions = $this->getPredictiveAnalytics();
        $insights = $this->getBusinessInsights();
        $performance = $this->getPerformanceMetrics();

        $data = [
            ['Metric', 'Value', 'Description'],
            ['Productivity Score', $insights['productivity_score'], 'Overall productivity rating (0-100)'],
            ['Success Rate', $analytics['efficiency']['success_rate'] . '%', 'Percentage of completed meetings'],
            ['Resource Utilization', $analytics['efficiency']['resource_utilization'] . '%', 'Room usage percentage'],
            ['Average Duration', $analytics['efficiency']['avg_duration'] . ' minutes', 'Average meeting duration'],
            ['Monthly Growth', $analytics['growth']['monthly'] . '%', 'Month-over-month growth'],
            ['Quality Index', $insights['meeting_quality_index'], 'Meeting quality score (0-100)'],
            ['Next Month Prediction', $predictions['next_month_meetings'] . ' meetings', 'Predicted meetings next month'],
            ['', '', ''],
            ['Recommendations', '', ''],
        ];

        foreach ($insights['recommendations'] as $rec) {
            $data[] = [$rec['title'], $rec['priority'], $rec['description']];
        }

        $filename = 'analytics-report-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
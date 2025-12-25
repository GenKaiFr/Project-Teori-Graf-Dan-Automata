<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;
use Carbon\Carbon;

class AnalyticsDataSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ada room dan participant
        $rooms = Room::all();
        $participants = Participant::all();

        if ($rooms->isEmpty() || $participants->isEmpty()) {
            $this->command->info('Rooms atau Participants kosong. Jalankan seeder lain terlebih dahulu.');
            return;
        }

        // Generate meeting data untuk 6 bulan terakhir
        $startDate = now()->subMonths(6);
        $endDate = now();

        $statuses = ['DRAFT', 'SCHEDULED', 'ONGOING', 'COMPLETED', 'CANCELLED'];
        $statusWeights = [10, 30, 5, 45, 10]; // Persentase untuk setiap status

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Skip weekend untuk simulasi meeting kantor
            if ($date->isWeekend()) {
                continue;
            }

            // Generate 2-8 meeting per hari kerja
            $meetingsPerDay = rand(2, 8);
            
            for ($i = 0; $i < $meetingsPerDay; $i++) {
                // Random waktu meeting (8 AM - 6 PM)
                $startHour = rand(8, 17);
                $startMinute = rand(0, 3) * 15; // 0, 15, 30, 45
                $duration = [30, 45, 60, 90, 120][rand(0, 4)]; // Durasi dalam menit

                $meetingStart = $date->copy()->setTime($startHour, $startMinute);
                $meetingEnd = $meetingStart->copy()->addMinutes($duration);

                // Pilih status berdasarkan weight
                $statusIndex = $this->weightedRandom($statusWeights);
                $status = $statuses[$statusIndex];

                // Untuk meeting masa lalu, pastikan status realistis
                if ($meetingStart < now()) {
                    if ($status === 'DRAFT') {
                        $status = 'CANCELLED';
                    } elseif ($status === 'SCHEDULED') {
                        $status = rand(0, 1) ? 'COMPLETED' : 'CANCELLED';
                    } elseif ($status === 'ONGOING') {
                        $status = 'COMPLETED';
                    }
                }

                $meeting = Meeting::create([
                    'title' => $this->generateMeetingTitle(),
                    'description' => $this->generateMeetingDescription(),
                    'start_time' => $meetingStart,
                    'end_time' => $meetingEnd,
                    'room_id' => $rooms->random()->id,
                    'status' => $status,
                    'created_at' => $meetingStart->copy()->subDays(rand(1, 7)),
                    'updated_at' => $meetingStart->copy()->subDays(rand(0, 3))
                ]);

                // Attach random participants (2-6 participants per meeting)
                $participantCount = rand(2, 6);
                $selectedParticipants = $participants->random($participantCount);
                $meeting->participants()->attach($selectedParticipants->pluck('id'));
            }
        }

        $this->command->info('Analytics data seeded successfully!');
    }

    private function weightedRandom($weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($weights as $index => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $index;
            }
        }
        
        return 0;
    }

    private function generateMeetingTitle()
    {
        $titles = [
            'Daily Standup Meeting',
            'Weekly Team Review',
            'Project Planning Session',
            'Client Presentation',
            'Budget Review Meeting',
            'Product Development Discussion',
            'Marketing Strategy Meeting',
            'Performance Review',
            'Training Session',
            'Board Meeting',
            'Quarterly Business Review',
            'Sprint Planning',
            'Retrospective Meeting',
            'All Hands Meeting',
            'Department Sync',
            'Vendor Meeting',
            'Technical Discussion',
            'Design Review',
            'Quality Assurance Meeting',
            'Risk Assessment Session'
        ];

        return $titles[array_rand($titles)];
    }

    private function generateMeetingDescription()
    {
        $descriptions = [
            'Diskusi rutin untuk update progress dan koordinasi tim',
            'Review hasil kerja minggu ini dan planning untuk minggu depan',
            'Perencanaan detail untuk project yang akan datang',
            'Presentasi proposal kepada klien potensial',
            'Evaluasi budget dan alokasi resource untuk quarter ini',
            'Brainstorming fitur baru dan roadmap product',
            'Strategi marketing untuk campaign mendatang',
            'Evaluasi performa individu dan tim',
            'Sesi training untuk skill development',
            'Meeting dengan board of directors',
            'Review pencapaian business metrics quarterly',
            'Planning untuk sprint development selanjutnya',
            'Retrospektif untuk improvement process',
            'Meeting seluruh karyawan untuk company update',
            'Sinkronisasi antar department',
            'Meeting dengan vendor dan supplier',
            'Diskusi teknis untuk problem solving',
            'Review design dan user experience',
            'Meeting quality assurance dan testing',
            'Assessment risiko project dan mitigation plan'
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
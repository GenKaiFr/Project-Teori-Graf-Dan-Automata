<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Participant;
use App\Models\Meeting;

class MeetingSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Create Users
        \App\Models\User::create([
            'name' => 'System Admin',
            'email' => 'admin@demo.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
        
        \App\Models\User::create([
            'name' => 'Meeting Manager',
            'email' => 'manager@demo.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'manager'
        ]);
        
        \App\Models\User::create([
            'name' => 'Regular User',
            'email' => 'user@demo.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'user'
        ]);
        
        // Create Rooms
        $rooms = [
            ['name' => 'Ruang A', 'capacity' => 20, 'facilities' => 'Proyektor, Whiteboard, AC'],
            ['name' => 'Ruang B', 'capacity' => 15, 'facilities' => 'TV, Sound System, AC'],
            ['name' => 'Ruang C', 'capacity' => 10, 'facilities' => 'Komputer, Webcam, AC'],
            ['name' => 'Ruang D', 'capacity' => 25, 'facilities' => 'Proyektor, TV, Sound System, AC'],
            ['name' => 'Ruang E', 'capacity' => 8, 'facilities' => 'Whiteboard, Webcam'],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }

        // Create Participants
        $participants = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'manager'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'participant'],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'role' => 'participant'],
            ['name' => 'Alice Brown', 'email' => 'alice@example.com', 'role' => 'participant'],
            ['name' => 'Charlie Davis', 'email' => 'charlie@example.com', 'role' => 'manager'],
            ['name' => 'Diana Prince', 'email' => 'diana@example.com', 'role' => 'participant'],
            ['name' => 'Edward Norton', 'email' => 'edward@example.com', 'role' => 'participant'],
            ['name' => 'Fiona Green', 'email' => 'fiona@example.com', 'role' => 'manager'],
            ['name' => 'George Miller', 'email' => 'george@example.com', 'role' => 'participant'],
            ['name' => 'Helen Carter', 'email' => 'helen@example.com', 'role' => 'participant'],
            ['name' => 'Ivan Petrov', 'email' => 'ivan@example.com', 'role' => 'participant'],
            ['name' => 'Julia Roberts', 'email' => 'julia@example.com', 'role' => 'manager'],
        ];

        foreach ($participants as $participant) {
            Participant::create($participant);
        }

        // Create Meetings with conflicts for testing
        $meetings = [
            // Hari ini - Konflik kompleks
            [
                'title' => 'Rapat Evaluasi Proyek Q4',
                'description' => 'Evaluasi progress dan hasil proyek quarter 4',
                'start_time' => now()->format('Y-m-d') . ' 09:00:00',
                'end_time' => now()->format('Y-m-d') . ' 10:30:00',
                'room_id' => 1,
                'status' => 'SCHEDULED',
                'participants' => [1, 2, 6]
            ],
            [
                'title' => 'Rapat Tim Development',
                'description' => 'Diskusi pengembangan fitur baru',
                'start_time' => now()->format('Y-m-d') . ' 09:30:00',
                'end_time' => now()->format('Y-m-d') . ' 11:00:00',
                'room_id' => 1, // Konflik ruangan
                'status' => 'DRAFT',
                'participants' => [3, 4, 7]
            ],
            [
                'title' => 'Budget Planning Meeting',
                'description' => 'Perencanaan budget untuk tahun depan',
                'start_time' => now()->format('Y-m-d') . ' 10:00:00',
                'end_time' => now()->format('Y-m-d') . ' 11:30:00',
                'room_id' => 2,
                'status' => 'SCHEDULED',
                'participants' => [1, 3, 8] // Konflik peserta
            ],
            [
                'title' => 'Marketing Strategy Review',
                'description' => 'Review strategi marketing Q1',
                'start_time' => now()->format('Y-m-d') . ' 10:15:00',
                'end_time' => now()->format('Y-m-d') . ' 11:45:00',
                'room_id' => 3,
                'status' => 'SCHEDULED',
                'participants' => [2, 4, 9] // Konflik peserta
            ],
            [
                'title' => 'Client Presentation',
                'description' => 'Presentasi proposal ke klien baru',
                'start_time' => now()->format('Y-m-d') . ' 13:00:00',
                'end_time' => now()->format('Y-m-d') . ' 14:30:00',
                'room_id' => 1,
                'status' => 'ONGOING',
                'participants' => [5, 6, 10]
            ],
            [
                'title' => 'HR Policy Update',
                'description' => 'Update kebijakan HR terbaru',
                'start_time' => now()->format('Y-m-d') . ' 13:30:00',
                'end_time' => now()->format('Y-m-d') . ' 15:00:00',
                'room_id' => 2,
                'status' => 'SCHEDULED',
                'participants' => [5, 7, 11] // Konflik peserta
            ],
            [
                'title' => 'Technical Architecture Review',
                'description' => 'Review arsitektur sistem baru',
                'start_time' => now()->format('Y-m-d') . ' 14:00:00',
                'end_time' => now()->format('Y-m-d') . ' 15:30:00',
                'room_id' => 1, // Konflik ruangan
                'status' => 'DRAFT',
                'participants' => [8, 9, 12]
            ],
            [
                'title' => 'Quality Assurance Meeting',
                'description' => 'Review proses QA dan testing',
                'start_time' => now()->format('Y-m-d') . ' 15:00:00',
                'end_time' => now()->format('Y-m-d') . ' 16:30:00',
                'room_id' => 3,
                'status' => 'SCHEDULED',
                'participants' => [3, 6, 9] // Konflik peserta
            ],
            
            // Besok - Lebih banyak konflik
            [
                'title' => 'Weekly Standup',
                'description' => 'Standup meeting mingguan tim',
                'start_time' => now()->addDay()->format('Y-m-d') . ' 09:00:00',
                'end_time' => now()->addDay()->format('Y-m-d') . ' 09:30:00',
                'room_id' => 1,
                'status' => 'SCHEDULED',
                'participants' => [1, 2, 3, 4]
            ],
            [
                'title' => 'Product Roadmap Planning',
                'description' => 'Perencanaan roadmap produk 2024',
                'start_time' => now()->addDay()->format('Y-m-d') . ' 09:15:00',
                'end_time' => now()->addDay()->format('Y-m-d') . ' 10:45:00',
                'room_id' => 2,
                'status' => 'DRAFT',
                'participants' => [1, 5, 8] // Konflik peserta
            ],
            [
                'title' => 'Security Audit Review',
                'description' => 'Review hasil audit keamanan sistem',
                'start_time' => now()->addDay()->format('Y-m-d') . ' 10:00:00',
                'end_time' => now()->addDay()->format('Y-m-d') . ' 11:30:00',
                'room_id' => 1, // Konflik ruangan
                'status' => 'SCHEDULED',
                'participants' => [6, 7, 10]
            ],
            [
                'title' => 'Customer Feedback Analysis',
                'description' => 'Analisis feedback pelanggan Q4',
                'start_time' => now()->addDay()->format('Y-m-d') . ' 10:30:00',
                'end_time' => now()->addDay()->format('Y-m-d') . ' 12:00:00',
                'room_id' => 3,
                'status' => 'SCHEDULED',
                'participants' => [2, 9, 11] // Konflik peserta
            ],
            [
                'title' => 'Database Migration Planning',
                'description' => 'Perencanaan migrasi database ke cloud',
                'start_time' => now()->addDay()->format('Y-m-d') . ' 14:00:00',
                'end_time' => now()->addDay()->format('Y-m-d') . ' 15:30:00',
                'room_id' => 2,
                'status' => 'DRAFT',
                'participants' => [3, 4, 12]
            ],
            [
                'title' => 'Performance Review Session',
                'description' => 'Sesi review performa karyawan',
                'start_time' => now()->addDay()->format('Y-m-d') . ' 14:30:00',
                'end_time' => now()->addDay()->format('Y-m-d') . ' 16:00:00',
                'room_id' => 1,
                'status' => 'SCHEDULED',
                'participants' => [5, 8, 10] // Konflik peserta
            ],
            
            // Minggu depan - Variasi status
            [
                'title' => 'Sprint Planning Meeting',
                'description' => 'Perencanaan sprint development',
                'start_time' => now()->addWeek()->format('Y-m-d') . ' 09:00:00',
                'end_time' => now()->addWeek()->format('Y-m-d') . ' 10:30:00',
                'room_id' => 1,
                'status' => 'DRAFT',
                'participants' => [1, 3, 6, 9]
            ],
            [
                'title' => 'Vendor Negotiation',
                'description' => 'Negosiasi kontrak dengan vendor',
                'start_time' => now()->addWeek()->format('Y-m-d') . ' 09:30:00',
                'end_time' => now()->addWeek()->format('Y-m-d') . ' 11:00:00',
                'room_id' => 2,
                'status' => 'SCHEDULED',
                'participants' => [2, 5, 7] // Konflik peserta
            ],
            [
                'title' => 'Training Session - New Tools',
                'description' => 'Pelatihan penggunaan tools baru',
                'start_time' => now()->addWeek()->format('Y-m-d') . ' 13:00:00',
                'end_time' => now()->addWeek()->format('Y-m-d') . ' 15:00:00',
                'room_id' => 3,
                'status' => 'SCHEDULED',
                'participants' => [4, 8, 11, 12]
            ],
            [
                'title' => 'Board Meeting Preparation',
                'description' => 'Persiapan materi untuk board meeting',
                'start_time' => now()->addWeek()->format('Y-m-d') . ' 14:00:00',
                'end_time' => now()->addWeek()->format('Y-m-d') . ' 16:00:00',
                'room_id' => 1,
                'status' => 'DRAFT',
                'participants' => [1, 5, 10] // Konflik peserta
            ],
            
            // Rapat yang sudah selesai
            [
                'title' => 'Monthly Review - November',
                'description' => 'Review bulanan November 2024',
                'start_time' => now()->subDays(3)->format('Y-m-d') . ' 10:00:00',
                'end_time' => now()->subDays(3)->format('Y-m-d') . ' 11:30:00',
                'room_id' => 1,
                'status' => 'COMPLETED',
                'participants' => [1, 2, 5, 8]
            ],
            [
                'title' => 'Team Building Discussion',
                'description' => 'Diskusi rencana team building',
                'start_time' => now()->subDays(2)->format('Y-m-d') . ' 15:00:00',
                'end_time' => now()->subDays(2)->format('Y-m-d') . ' 16:00:00',
                'room_id' => 2,
                'status' => 'COMPLETED',
                'participants' => [3, 4, 6, 7, 9]
            ],
            [
                'title' => 'Emergency Bug Fix Meeting',
                'description' => 'Rapat darurat untuk bug critical',
                'start_time' => now()->subDay()->format('Y-m-d') . ' 16:00:00',
                'end_time' => now()->subDay()->format('Y-m-d') . ' 17:00:00',
                'room_id' => 3,
                'status' => 'CANCELLED',
                'participants' => [2, 3, 8, 11]
            ],
            
            // Rapat masa depan
            [
                'title' => 'Quarterly Business Review',
                'description' => 'Review bisnis triwulanan',
                'start_time' => now()->addWeeks(2)->format('Y-m-d') . ' 09:00:00',
                'end_time' => now()->addWeeks(2)->format('Y-m-d') . ' 12:00:00',
                'room_id' => 1,
                'status' => 'DRAFT',
                'participants' => [1, 5, 8, 12]
            ],
            [
                'title' => 'New Employee Onboarding',
                'description' => 'Orientasi karyawan baru',
                'start_time' => now()->addWeeks(2)->format('Y-m-d') . ' 10:00:00',
                'end_time' => now()->addWeeks(2)->format('Y-m-d') . ' 14:00:00',
                'room_id' => 2,
                'status' => 'SCHEDULED',
                'participants' => [6, 7, 9, 10] // Konflik peserta
            ]
        ];

        foreach ($meetings as $meetingData) {
            $participants = $meetingData['participants'];
            unset($meetingData['participants']);
            
            $meeting = Meeting::create($meetingData);
            $meeting->participants()->attach($participants);
        }
    }
}
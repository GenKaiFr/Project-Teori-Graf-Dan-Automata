<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use App\Models\Participant;
use App\Models\MeetingTemplate;
use App\Services\ConflictGraphService;
use App\Services\MeetingAutomataService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MeetingController extends Controller
{
    private ConflictGraphService $conflictGraph;

    public function __construct()
    {
        $this->conflictGraph = new ConflictGraphService();
    }

    public function index(): View
    {
        $meetings = Meeting::with(['room', 'participants'])->orderBy('start_time')->get();
        $rooms = Room::all();
        return view('meetings.index', compact('meetings', 'rooms'));
    }

    public function create(): View
    {
        $rooms = Room::all();
        $participants = Participant::all();
        $templates = MeetingTemplate::where('is_active', true)->orderBy('name')->get();
        return view('meetings.create', compact('rooms', 'participants', 'templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'participants' => 'array'
        ]);

        $allMeetings = CacheService::getMeetingsData();
        $this->conflictGraph->buildGraph($allMeetings);

        $conflicts = $this->conflictGraph->hasConflict($validated);
        if ($conflicts) {
            return back()->withErrors(['Konflik jadwal terdeteksi dengan rapat lain.'])
                        ->with('conflicts', $conflicts)
                        ->withInput();
        }

        $validated['status'] = MeetingAutomataService::SCHEDULED;
        $meeting = Meeting::create($validated);
        
        if (!empty($validated['participants'])) {
            $meeting->participants()->attach($validated['participants']);
        }

        CacheService::clearMeetingsCache();
        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dibuat.');
    }

    public function show(Meeting $meeting): View
    {
        $meeting->load(['room', 'participants']);
        return view('meetings.show', compact('meeting'));
    }

    public function edit(Meeting $meeting): View
    {
        $meeting->load('participants');
        $rooms = Room::all();
        $participants = Participant::all();
        return view('meetings.edit', compact('meeting', 'rooms', 'participants'));
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:DRAFT,SCHEDULED,ONGOING,COMPLETED,CANCELLED',
            'participants' => 'array'
        ]);

        if ($validated['status'] == MeetingAutomataService::SCHEDULED) {
            $allMeetings = Meeting::with('room')->where('id', '!=', $meeting->id)->get();
            $this->conflictGraph->buildGraph($allMeetings);
            
            $conflicts = $this->conflictGraph->hasConflict($validated);
            if ($conflicts) {
                return back()->withErrors(['Konflik jadwal terdeteksi dengan rapat lain.'])
                            ->with('conflicts', $conflicts)
                            ->withInput();
            }
        }

        $meeting->update($validated);
        $meeting->participants()->sync($validated['participants'] ?? []);

        CacheService::clearMeetingsCache();
        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil diperbarui.');
    }

    public function destroy(Meeting $meeting): RedirectResponse
    {
        $meeting->participants()->detach();
        $meeting->delete();
        CacheService::clearMeetingsCache();
        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dihapus.');
    }

    public function updateStatus(Request $request, Meeting $meeting): RedirectResponse
    {
        $newStatus = $request->input('status');
        
        $result = MeetingAutomataService::transition($meeting->status, $newStatus);
        if ($result !== true) {
            return back()->withErrors([$result]);
        }

        $meeting->update(['status' => $newStatus]);
        return redirect()->route('meetings.index')->with('success', 'Status rapat berhasil diperbarui.');
    }

    public function checkConflict(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'room_id' => 'required|exists:rooms,id',
            'exclude_id' => 'nullable|integer'
        ]);

        $query = Meeting::with('room');
        if (!empty($data['exclude_id'])) {
            $query->where('id', '!=', $data['exclude_id']);
        }
        
        $allMeetings = $query->get();
        $this->conflictGraph->buildGraph($allMeetings);
        
        $conflicts = $this->conflictGraph->hasConflict($data);

        return response()->json([
            'has_conflict' => $conflicts !== false,
            'conflicts' => $conflicts ?: []
        ]);
    }

    public function getGraphData(): JsonResponse
    {
        $meetings = Meeting::with(['room', 'participants'])
                          ->whereIn('status', ['DRAFT', 'SCHEDULED', 'ONGOING'])
                          ->orderBy('start_time')
                          ->get();
        
        if ($meetings->isEmpty()) {
            return response()->json(['nodes' => [], 'links' => []]);
        }
        
        $this->conflictGraph->buildGraph($meetings);
        $graphData = $this->conflictGraph->getGraphData();
        
        // Enhance node data with additional information
        foreach ($graphData['nodes'] as &$node) {
            $meeting = $meetings->find($node['id']);
            if ($meeting) {
                $node['title'] = $meeting->title;
                $node['description'] = $meeting->description ?? '';
                $node['status'] = $meeting->status;
                $node['room_name'] = $meeting->room->name ?? 'Unknown';
                $node['start_time'] = $meeting->start_time->format('d/m H:i');
                $node['end_time'] = $meeting->end_time->format('H:i');
                $node['duration'] = $meeting->start_time->diffInMinutes($meeting->end_time) . ' menit';
                $node['participants_count'] = $meeting->participants->count();
                $node['full_start_time'] = $meeting->start_time->format('Y-m-d H:i:s');
                $node['full_end_time'] = $meeting->end_time->format('Y-m-d H:i:s');
            }
        }
        
        return response()->json($graphData);
    }

    public function dashboard(): View
    {
        try {
            $stats = CacheService::getStatistics();
        } catch (\Exception $e) {
            // Fallback for testing environment
            $stats = [
                'total_meetings' => Meeting::count(),
                'meetings_this_month' => Meeting::whereMonth('start_time', now()->month)->count(),
                'meetings_today' => Meeting::whereDate('start_time', today())->count(),
                'upcoming_meetings' => Meeting::where('start_time', '>', now())
                    ->whereIn('status', ['DRAFT', 'SCHEDULED'])->count(),
            ];
        }
        
        $stats['total'] = $stats['total_meetings'];
        $stats['today'] = $stats['meetings_today'];
        $stats['upcoming'] = $stats['upcoming_meetings'];
        $stats['conflicts'] = Meeting::where('status', 'DRAFT')->count();
        
        $recentMeetings = Meeting::with(['room', 'participants'])
                                ->orderBy('start_time', 'desc')
                                ->limit(5)
                                ->get();

        return view('meetings.dashboard', compact('stats', 'recentMeetings'));
    }

    public function bulk(): View
    {
        $meetings = Meeting::with(['room', 'participants'])
                          ->when(request('room_id'), fn($q) => $q->where('room_id', request('room_id')))
                          ->when(request('status'), fn($q) => $q->where('status', request('status')))
                          ->when(request('date'), fn($q) => $q->whereDate('start_time', request('date')))
                          ->orderBy('start_time')
                          ->get();
        
        $rooms = Room::all();
        
        return view('meetings.bulk', compact('meetings', 'rooms'));
    }

    public function calendar(): View
    {
        return view('meetings.calendar');
    }

    public function graph(): View
    {
        return view('meetings.graph');
    }

    public function getCalendarData(): JsonResponse
    {
        $meetings = Meeting::with(['room', 'participants'])->get();
        return response()->json($meetings);
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meeting_ids' => 'required|array',
            'meeting_ids.*' => 'exists:meetings,id',
            'status' => 'required|in:DRAFT,SCHEDULED,ONGOING,COMPLETED,CANCELLED'
        ]);

        $successCount = 0;
        $errorCount = 0;

        foreach ($validated['meeting_ids'] as $id) {
            $meeting = Meeting::find($id);
            if ($meeting) {
                $result = MeetingAutomataService::transition($meeting->status, $validated['status']);
                if ($result === true) {
                    $meeting->update(['status' => $validated['status']]);
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        }

        if ($successCount > 0) {
            session()->flash('success', "Berhasil memperbarui {$successCount} rapat.");
        }
        if ($errorCount > 0) {
            session()->flash('error', "Gagal memperbarui {$errorCount} rapat.");
        }

        return back();
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meeting_ids' => 'required|array',
            'meeting_ids.*' => 'exists:meetings,id'
        ]);

        $deleted = Meeting::whereIn('id', $validated['meeting_ids'])->delete();
        
        return back()->with('success', "Berhasil menghapus {$deleted} rapat.");
    }

    public function exportMeetings(Request $request)
    {
        $ids = explode(',', $request->get('ids', ''));
        $meetings = Meeting::with(['room', 'participants'])->whereIn('id', $ids)->get();

        if ($meetings->isEmpty()) {
            return back()->withErrors(['Tidak ada data untuk di-export.']);
        }

        $filename = 'meetings_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($meetings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Judul', 'Deskripsi', 'Waktu Mulai', 'Waktu Selesai', 'Ruangan', 'Status', 'Peserta']);
            
            foreach ($meetings as $meeting) {
                fputcsv($file, [
                    $meeting->id,
                    $meeting->title,
                    $meeting->description,
                    $meeting->start_time->format('Y-m-d H:i:s'),
                    $meeting->end_time->format('Y-m-d H:i:s'),
                    $meeting->room->name ?? '',
                    $meeting->status,
                    $meeting->participants->pluck('name')->implode(', ')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
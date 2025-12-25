<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('reports.index', compact('rooms'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'room_id' => 'nullable|exists:rooms,id',
            'status' => 'nullable|in:DRAFT,SCHEDULED,ONGOING,COMPLETED,CANCELLED'
        ]);

        $query = Meeting::with(['room', 'participants'])
            ->whereBetween('start_time', [$request->start_date, $request->end_date]);

        if ($request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $meetings = $query->orderBy('start_time')->get();
        $room = $request->room_id ? Room::find($request->room_id) : null;

        $data = [
            'meetings' => $meetings,
            'start_date' => Carbon::parse($request->start_date)->format('d/m/Y'),
            'end_date' => Carbon::parse($request->end_date)->format('d/m/Y'),
            'room' => $room,
            'status' => $request->status,
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('reports.pdf', $data);
        
        $filename = 'laporan_rapat_' . 
                   Carbon::parse($request->start_date)->format('Y-m-d') . '_to_' . 
                   Carbon::parse($request->end_date)->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'room_id' => 'nullable|exists:rooms,id',
            'status' => 'nullable|in:DRAFT,SCHEDULED,ONGOING,COMPLETED,CANCELLED'
        ]);

        $query = Meeting::with(['room', 'participants'])
            ->whereBetween('start_time', [$request->start_date, $request->end_date]);

        if ($request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $meetings = $query->orderBy('start_time')->get();

        $filename = 'laporan_rapat_' . 
                   Carbon::parse($request->start_date)->format('Y-m-d') . '_to_' . 
                   Carbon::parse($request->end_date)->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($meetings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Judul', 'Deskripsi', 'Waktu Mulai', 'Waktu Selesai', 'Ruangan', 'Status', 'Peserta', 'Durasi (menit)']);
            
            foreach ($meetings as $meeting) {
                $duration = $meeting->start_time->diffInMinutes($meeting->end_time);
                fputcsv($file, [
                    $meeting->id,
                    $meeting->title,
                    $meeting->description,
                    $meeting->start_time->format('d/m/Y H:i'),
                    $meeting->end_time->format('d/m/Y H:i'),
                    $meeting->room->name ?? '',
                    $meeting->status,
                    $meeting->participants->pluck('name')->implode(', '),
                    $duration
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::withCount('meetings')->get();
        return view('rooms.index', compact('rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string'
        ]);

        Room::create($validated);
        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|string'
        ]);

        $room->update($validated);
        return back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();
        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    public function show(Room $room): JsonResponse
    {
        return response()->json($room);
    }
}
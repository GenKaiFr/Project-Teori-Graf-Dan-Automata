<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ParticipantController extends Controller
{
    public function index(): View
    {
        $participants = Participant::withCount('meetings')->get();
        return view('participants.index', compact('participants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:participants,email',
            'role' => 'required|string|max:50'
        ]);

        Participant::create($validated);
        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function update(Request $request, Participant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:participants,email,' . $participant->id,
            'role' => 'required|string|max:50'
        ]);

        $participant->update($validated);
        return back()->with('success', 'Peserta berhasil diperbarui.');
    }

    public function destroy(Participant $participant): RedirectResponse
    {
        $participant->delete();
        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function show(Participant $participant): JsonResponse
    {
        return response()->json($participant);
    }
}
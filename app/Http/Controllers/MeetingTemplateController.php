<?php

namespace App\Http\Controllers;

use App\Models\MeetingTemplate;
use App\Models\Participant;
use Illuminate\Http\Request;

class MeetingTemplateController extends Controller
{
    public function index()
    {
        $templates = MeetingTemplate::with('creator')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $participants = Participant::orderBy('name')->get();
        return view('templates.create', compact('participants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'default_participants' => 'nullable|array'
        ]);

        MeetingTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'default_participants' => $request->default_participants,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template berhasil dibuat!');
    }

    public function show(MeetingTemplate $template)
    {
        $template->load('creator');
        return response()->json($template);
    }

    public function edit(MeetingTemplate $template)
    {
        $participants = Participant::orderBy('name')->get();
        return view('templates.edit', compact('template', 'participants'));
    }

    public function update(Request $request, MeetingTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'default_participants' => 'nullable|array'
        ]);

        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'default_participants' => $request->default_participants
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template berhasil diperbarui!');
    }

    public function destroy(MeetingTemplate $template)
    {
        $template->update(['is_active' => false]);
        
        return redirect()->route('templates.index')
            ->with('success', 'Template berhasil dihapus!');
    }
}
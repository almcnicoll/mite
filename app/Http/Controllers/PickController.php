<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\Pick;
use App\Models\User;
use Illuminate\Http\Request;

class PickController extends Controller
{
    public function index()
    {
        $picks = Pick::with(['user', 'cause'])
            ->orderByDesc('date')
            ->paginate(30);

        return view('picks.index', compact('picks'));
    }

    // The main daily screen — loads or creates today's pick
    public function today()
    {
        $pick   = Pick::todayOrCreate();
        $causes = Cause::orderBy('name')->get();
        $users  = User::orderBy('rotation_order')->get();

        return view('picks.today', compact('pick', 'causes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'       => 'required|date|unique:picks,date',
            'cause_id'   => 'nullable|exists:causes,id',
            'user_id'    => 'nullable|exists:users,id',
            'guest_name' => 'nullable|string|max:255',
        ]);

        Pick::create($validated);

        return redirect()->route('picks.today')->with('success', 'Pick saved.');
    }

    // Update an existing pick — used for both same-day and retrospective choices
    public function update(Request $request, Pick $pick)
    {
        $validated = $request->validate([
            'cause_id'   => 'nullable|exists:causes,id',
            'user_id'    => 'nullable|exists:users,id',
            'guest_name' => 'nullable|string|max:255',
        ]);

        $pick->update($validated);

        return redirect()->route('picks.today')->with('success', 'Pick updated.');
    }

    public function edit(Pick $pick)
    {
        $causes = Cause::orderBy('name')->get();
        $users  = User::orderBy('rotation_order')->get();

        return view('picks.edit', compact('pick', 'causes', 'users'));
    }
}
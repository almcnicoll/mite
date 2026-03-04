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
        $pick    = Pick::todayOrCreate();
        $causes  = Cause::orderBy('name')->get();
        $users   = User::orderBy('rotation_order')->get();

        $outstanding = Pick::whereNull('cause_id')
            ->where('date', '<', today()->toDateString())
            ->orderBy('date')
            ->with('user')
            ->get();

        // Build 5 featured causes for the circle UI
        $featured = collect();

        if ($pick->user_id) {
            $featured = Pick::where('user_id', $pick->user_id)
                ->whereNotNull('cause_id')
                ->orderByDesc('date')
                ->with('cause')
                ->get()
                ->pluck('cause')
                ->unique('id')
                ->take(5);
        }

        // Fill remaining slots with random causes not already featured
        $featuredIds = $featured->pluck('id')->toArray();
        $filler = $causes->whereNotIn('id', $featuredIds)
            ->shuffle()
            ->take(5 - $featured->count());

        $featured = $featured->concat($filler);

        // Pad to exactly 5 with nulls (shown as grey empty circles)
        while ($featured->count() < 5) {
            $featured->push(null);
        }

        return view('picks.today', compact(
            'pick', 'causes', 'users', 'outstanding', 'featured'
        ));
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
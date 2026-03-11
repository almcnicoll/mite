<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\Pick;
use App\Models\User;
use App\Models\Setup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function today(?string $date = null)
    {
        if (!Setup::where('date_from', '<=', today())->exists()) {
            return redirect()->route('setup.index')
                ->with('error', 'Please create a setup record with a daily amount before making picks.');
        }

        $targetDate = $date ? \Carbon\Carbon::parse($date) : today();
        $isToday    = $targetDate->isSameDay(today());

        // For past dates, the pick must already exist — don't create one
        if ($isToday) {
            $pick = Pick::todayOrCreate();
        } else {
            $pick = Pick::where('date', $targetDate->toDateString())->first();
            if (!$pick) {
                return redirect()->route('picks.today')
                    ->with('error', 'No pick found for ' . $targetDate->format('j M Y') . '.');
            }
        }

        if (!$pick) {
            return redirect()->route('users.create')
                ->with('error', 'Please add at least one user before making picks.');
        }

        // Relative date label for display in view
        $dateLabel = null;
        if (!$isToday) {
            $daysAgo = (int) $targetDate->diffInDays(today());
            $dateLabel = $daysAgo === 1 ? 'yesterday' : $daysAgo . ' days ago';
        }

        $causes  = Cause::orderBy('name')->get();
        $users   = User::orderBy('rotation_order')->get();

        $outstanding = Pick::whereNull('cause_id')
            ->whereNotNull('user_id')                    // exclude any orphaned guest-looking picks
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

        while ($featured->count() < 5) {
            $featured->push(null);
        }

        return view('picks.today', compact(
            'pick', 'causes', 'users', 'outstanding', 'featured',
            'targetDate', 'isToday', 'dateLabel'
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

    public function resetConfirm()
    {
        $count = Pick::whereNull('cause_id')
            ->whereNotNull('user_id')
            ->where('date', '<', today()->toDateString())
            ->count();

        return view('picks.reset', compact('count'));
    }

    public function resetExecute()
    {
        $deleted = Pick::whereNull('cause_id')
            ->whereNotNull('user_id')
            ->where('date', '<', today()->toDateString())
            ->delete();

        return redirect()->route('picks.today')
            ->with('success', $deleted . ' outstanding ' . Str::plural('pick', $deleted) . ' removed.');
    }

    public function destroy(Pick $pick)
    {
        $pick->delete();
        return redirect()->route('picks.index')->with('success', 'Pick deleted.');
    }
}
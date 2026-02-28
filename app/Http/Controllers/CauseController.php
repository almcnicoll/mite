<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use Illuminate\Http\Request;

class CauseController extends Controller
{
    public function index()
    {
        $causes = Cause::orderBy('name')->get();
        return view('causes.index', compact('causes'));
    }

    public function create()
    {
        return view('causes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email',
            'notes'   => 'nullable|string',
            'colour'  => 'nullable|string|size:7',
            'picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('picture')) {
            $validated['picture'] = $request->file('picture')->store('pictures', 'public');
        }

        Cause::create($validated);

        return redirect()->route('causes.index')->with('success', 'Cause added.');
    }

    public function show(Cause $cause)
    {
        return view('causes.show', [
            'cause'        => $cause,
            'totalPicked'  => $cause->totalPicked(),
            'totalDonated' => $cause->totalDonated(),
            'balance'      => $cause->balance(),
            'donations'    => $cause->donations()->orderByDesc('date_paid')->get(),
            'picks'        => $cause->picks()->orderByDesc('date')->get(),
        ]);
    }

    public function edit(Cause $cause)
    {
        return view('causes.edit', compact('cause'));
    }

    public function update(Request $request, Cause $cause)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email',
            'notes'   => 'nullable|string',
            'colour'  => 'nullable|string|size:7',
            'picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('picture')) {
            $validated['picture'] = $request->file('picture')->store('pictures', 'public');
        }

        $cause->update($validated);

        return redirect()->route('causes.index')->with('success', 'Cause updated.');
    }

    public function destroy(Cause $cause)
    {
        $cause->delete();
        return redirect()->route('causes.index')->with('success', 'Cause removed.');
    }
}
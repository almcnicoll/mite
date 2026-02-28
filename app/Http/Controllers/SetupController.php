<?php

namespace App\Http\Controllers;

use App\Models\Setup;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function index()
    {
        $setups = Setup::orderByDesc('date_from')->get();
        return view('setup.index', compact('setups'));
    }

    public function create()
    {
        return view('setup.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_from'      => 'required|date|unique:setups,date_from',
            'amount_per_day' => 'required|numeric|min:0.01',
        ]);

        Setup::create($validated);

        return redirect()->route('setup.index')->with('success', 'Setup record added.');
    }
}
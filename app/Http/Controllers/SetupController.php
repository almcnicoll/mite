<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Setup;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function index()
    {
        $setups = Setup::orderByDesc('date_from')->get();
        $threshold = Setting::donationThreshold();
        return view('setup.index', compact('setups', 'threshold'));
    }

    public function updateThreshold(Request $request)
    {
        $validated = $request->validate(['donation_threshold' => 'required|numeric|min:0']);

        Setting::setDonationThreshold((float) $validated['donation_threshold']);

        return redirect()->route('setup.index')->with('success', 'Donation threshold updated.');
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
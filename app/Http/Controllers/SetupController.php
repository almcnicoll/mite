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
        $threshold         = Setting::donationThreshold();
        $reminderThreshold = Setting::reminderThreshold();
        $remindersOn       = Setting::reminderEmailsEnabled();
        return view('setup.index', compact('setups', 'threshold', 'reminderThreshold', 'remindersOn'));
    }

    public function updateReminderThreshold(Request $request)
    {
        $validated = $request->validate([
            'reminder_emails_enabled' => 'required|boolean',
            'reminder_threshold'      => 'required|integer|min:0|max:1000',
        ]);

        Setting::setReminderEmailsEnabled((bool) $validated['reminder_emails_enabled']);
        Setting::setReminderThreshold((int) $validated['reminder_threshold']);

        return redirect()->route('setup.index')->with('success', 'Reminder settings updated.');
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
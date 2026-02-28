<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\Donation;
use App\Models\Setup;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index()
    {
        $donations = Donation::with('cause')
            ->orderByDesc('date_paid')
            ->paginate(30);

        return view('donations.index', compact('donations'));
    }

    public function create(Request $request)
    {
        $causes = Cause::orderBy('name')->get();
        $suggested = $request->cause_id
            ? $causes->firstWhere('id', $request->cause_id)
            : $causes->sortByDesc(fn($c) => $c->balance())->first();

        return view('donations.create', compact('causes', 'suggested'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cause_id'  => 'required|exists:causes,id',
            'date_paid' => 'required|date',
            'amount'    => 'required|numeric|min:0.01',
        ]);

        Donation::create($validated);

        return redirect()->route('donations.index')->with('success', 'Donation recorded.');
    }

    public function destroy(Donation $donation)
    {
        $donation->delete();
        return redirect()->route('donations.index')->with('success', 'Donation removed.');
    }
}
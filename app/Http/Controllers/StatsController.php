<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\Donation;
use App\Models\Pick;
use App\Models\Setup;
use Illuminate\Support\Collection;

class StatsController extends Controller
{
    public function index()
    {
        // Build a list of setups ordered by date_from ascending so we can
        // do period-based rate lookups efficiently.
        $setups = Setup::orderBy('date_from')->get();

        // Helper: given a pick date, return the amount_per_day that was in
        // force on that date (i.e. the most recent setup whose date_from is
        // <= the pick date).
        $rateForDate = function (string $date) use ($setups): float {
            $rate = 0.0;
            foreach ($setups as $setup) {
                if ($setup->date_from <= $date) {
                    $rate = (float) $setup->amount_per_day;
                } else {
                    break;
                }
            }
            return $rate;
        };

        // Accumulate the earned total per cause_id from all picks.
        // Picks with no cause (cause_id IS NULL) are skipped — they represent
        // days not yet assigned, and we don't want to inflate any bar.
        $earnedByCause = [];   // cause_id => float

        Pick::whereNotNull('cause_id')
            ->orderBy('date')
            ->get(['cause_id', 'date'])
            ->each(function ($pick) use (&$earnedByCause, $rateForDate) {
                $cid  = $pick->cause_id;
                $rate = $rateForDate($pick->date);
                $earnedByCause[$cid] = ($earnedByCause[$cid] ?? 0.0) + $rate;
            });

        // Total donations already paid, per cause.
        $donatedByCause = Donation::selectRaw('cause_id, SUM(amount) as total')
            ->groupBy('cause_id')
            ->pluck('total', 'cause_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        // Merge everything against the Causes table so we always have every
        // cause represented (even those with zero picks yet).
        $causes = Cause::orderBy('name')->get(['id', 'name', 'colour']);

        $allTimeData   = [];
        $nextDonationData = [];

        foreach ($causes as $cause) {
            $earned  = $earnedByCause[$cause->id]  ?? 0.0;
            $donated = $donatedByCause[$cause->id] ?? 0.0;
            $pending = max(0.0, $earned - $donated);

            $allTimeData[]   = [
                'label'  => $cause->name,
                'amount' => round($earned, 2),
                'colour' => $cause->colour ?? '#2980b9',
            ];

            $nextDonationData[] = [
                'label'  => $cause->name,
                'amount' => round($pending, 2),
                'colour' => $cause->colour ?? '#2980b9',
            ];
        }

        // Sort each set descending by amount.
        usort($allTimeData,      fn ($a, $b) => $b['amount'] <=> $a['amount']);
        usort($nextDonationData, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return view('stats.index', compact('allTimeData', 'nextDonationData'));
    }
}

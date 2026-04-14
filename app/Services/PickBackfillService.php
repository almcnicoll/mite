<?php

namespace App\Services;

use App\Models\Pick;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PickBackfillService
{
    public function backfill(): void
    {
        $users = User::orderBy('rotation_order')->get();

        if ($users->isEmpty()) {
            return;
        }

        $earliest = Pick::min('date');

        if (!$earliest) {
            return;
        }

        // Build a set of all dates that already have a pick
        $existing = Pick::pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->flip()
            ->toArray();

        // Walk every date from the earliest pick up to yesterday
        $period = CarbonPeriod::create($earliest, today()->subDay());

        foreach ($period as $date) {
            $dateStr = $date->toDateString();

            if (isset($existing[$dateStr])) {
                continue;
            }

            // Determine who should pick on this date by finding the last
            // user pick strictly before this date and incrementing rotation
            $lastUserPick = Pick::whereNotNull('user_id')
                ->where('date', '<', $dateStr)
                ->orderByDesc('date')
                ->first();

            $maxOrder = $users->max('rotation_order');

            if (!$lastUserPick) {
                $nextUser = $users->first();
            } else {
                $lastOrder = $users->firstWhere('id', $lastUserPick->user_id)?->rotation_order ?? 0;
                $nextOrder = $lastOrder >= $maxOrder ? 1 : $lastOrder + 1;
                $nextUser  = $users->firstWhere('rotation_order', $nextOrder);
            }

            if (!$nextUser) {
                continue;
            }

            Pick::create([
                'date'    => $dateStr,
                'user_id' => $nextUser->id,
            ]);

            // Add the new pick to our existing set so subsequent dates
            // in the loop see it when determining the next rotation
            $existing[$dateStr] = true;
        }
    }
}
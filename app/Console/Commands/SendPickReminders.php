<?php

namespace App\Console\Commands;

use App\Mail\PickReminder;
use App\Models\Pick;
use App\Models\Setting;
use App\Models\User;
use App\Services\PickBackfillService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPickReminders extends Command
{
    protected $signature = 'picks:send-reminders';

    protected $description = 'Email each person who has the threshold number of outstanding picks (at most weekly).';

    public function handle(PickBackfillService $backfill): int
    {
        if (!Setting::reminderEmailsEnabled()) {
            $this->info('Reminder emails are switched off (Setup → Enable reminder emails).');
            return self::SUCCESS;
        }

        $threshold = Setting::reminderThreshold();

        if ($threshold < 1) {
            $this->info('Reminders are off (threshold is 0).');
            return self::SUCCESS;
        }

        // Missed days only become outstanding picks once backfilled (normally on a visit to the daily screen).
        $backfill->backfill();

        $counts = Pick::whereNull('cause_id')
            ->whereNotNull('user_id')
            ->where('date', '<', today()->toDateString())
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $sent = 0;
        $failed = 0;

        $users = User::whereIn('id', $counts->filter(fn ($n) => $n >= $threshold)->keys())
            ->where('reminders_enabled', true)
            ->get();

        foreach ($users as $user) {
            // At most one a week: wait until the start of the 7th day after the last one.
            if ($user->last_reminded_at && $user->last_reminded_at->copy()->addDays(7)->startOfDay()->gt(now())) {
                continue;
            }

            try {
                Mail::to($user)->send(new PickReminder($user, (int) $counts[$user->id]));
                $user->forceFill(['last_reminded_at' => now()])->save();
                $sent++;
            } catch (Throwable $e) {
                // Don't mark as reminded, so the next run retries; keep going for everyone else.
                report($e);
                $this->error("Could not email {$user->name}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Reminders sent: {$sent}" . ($failed ? ", failed: {$failed}" : ''));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}

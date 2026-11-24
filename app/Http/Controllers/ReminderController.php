<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * One-click opt-out target for the link in reminder emails. The routes are `signed`, so no
 * "who are you?" choice is needed. GET only asks for confirmation (mail scanners prefetch links);
 * the POST does the opting out.
 */
class ReminderController extends Controller
{
    public function show(User $user)
    {
        return view('reminders.unsubscribe', ['user' => $user, 'done' => false]);
    }

    public function store(User $user)
    {
        $user->update(['reminders_enabled' => false]);

        return view('reminders.unsubscribe', ['user' => $user, 'done' => true]);
    }
}

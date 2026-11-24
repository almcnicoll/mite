<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Who are you?" — no security, just a device-level choice stored in a cookie.
 * Exposes $currentUser (nullable) and $people (all users) to views, and sends devices
 * with no valid choice to the person chooser. If no users exist yet, lets everything
 * through so the first user can be created.
 */
class CurrentPerson
{
    public const COOKIE = 'mite_person';

    public function handle(Request $request, Closure $next): Response
    {
        $people = User::orderBy('rotation_order')->get();
        $user   = $people->firstWhere('id', $request->cookie(self::COOKIE));

        $request->attributes->set('currentUser', $user);
        View::share('currentUser', $user);
        View::share('people', $people);

        if (!$user && $people->isNotEmpty() && !$request->routeIs('person.*', 'reminders.*')) {
            return redirect()->guest(route('person.choose'));
        }

        return $next($request);
    }
}

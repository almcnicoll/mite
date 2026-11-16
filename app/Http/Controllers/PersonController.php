<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CurrentPerson;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function choose()
    {
        return view('person.choose');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);

        // Five years: effectively "until changed".
        $cookie = cookie(CurrentPerson::COOKIE, $validated['user_id'], 60 * 24 * 365 * 5);

        // Coming from the chooser page → go where the user was originally heading;
        // coming from the nav dropdown → stay on the same page.
        $redirect = url()->previous() === route('person.choose')
            ? redirect()->intended('/')
            : back();

        return $redirect->withCookie($cookie);
    }
}

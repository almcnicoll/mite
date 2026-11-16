<?php

namespace Tests;

use App\Http\Middleware\CurrentPerson;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Make subsequent requests come from a device that has chosen $user in the "who are you?" prompt. */
    protected function actingAsPerson(User $user): static
    {
        return $this->withCookie(CurrentPerson::COOKIE, (string) $user->id);
    }

    protected function makeUser(string $name, int $order, string $colour = null): User
    {
        return User::create([
            'name' => $name, 'email' => strtolower($name) . '@example.com',
            'rotation_order' => $order, 'password' => 'x', 'colour' => $colour,
        ]);
    }
}

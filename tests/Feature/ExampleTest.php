<?php

namespace Tests\Feature;

use App\Models\Setup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_setup_when_no_setup_exists(): void
    {
        // No users yet, so the "who are you?" prompt is skipped.
        $this->get('/')->assertRedirect(route('setup.index'));
    }

    public function test_home_loads_once_setup_and_user_exist(): void
    {
        Setup::create(['date_from' => '2024-01-01', 'amount_per_day' => 5]);
        $user = $this->makeUser('Test', 1);

        $this->actingAsPerson($user)->get('/')->assertOk();
    }
}

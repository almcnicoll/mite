<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_setup_when_no_setup_exists(): void
    {
        $this->get('/')->assertRedirect(route('setup.index'));
    }

    public function test_home_loads_once_setup_and_user_exist(): void
    {
        \App\Models\Setup::create(['date_from' => '2024-01-01', 'amount_per_day' => 5]);
        \App\Models\User::create([
            'name' => 'Test', 'email' => 't@example.com', 'rotation_order' => 1, 'password' => 'x',
        ]);

        $this->get('/')->assertOk();
    }
}

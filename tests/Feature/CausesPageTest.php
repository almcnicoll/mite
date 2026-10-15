<?php

namespace Tests\Feature;

use App\Models\Cause;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CausesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_causes_render_as_tiles_alphabetically_with_sort_selector(): void
    {
        Cause::create(['name' => 'Zebra Trust']);
        Cause::create(['name' => 'Alpha Aid']);

        $this->get('/causes')->assertOk()
            ->assertSee('cause-grid', false)
            ->assertSeeInOrder(['Alpha Aid', 'Zebra Trust'])
            ->assertSee('Date added')
            ->assertSee('Amount raised')
            ->assertSee("miteTabs('causes', 'name'", false);
    }

    public function test_empty_state(): void
    {
        $this->get('/causes')->assertOk()->assertSee('No causes yet');
    }

    public function test_stats_uses_shared_selector(): void
    {
        $this->get('/stats')->assertOk()
            ->assertSee("miteTabs('stats', 'alltime'", false)
            ->assertSee('data-tab="next"', false);
    }
}

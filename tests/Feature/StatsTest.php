<?php

namespace Tests\Feature;

use App\Models\Cause;
use App\Models\Pick;
use App\Models\Setting;
use App\Models\Setup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    private function causeWithEarnings(string $name, int $days): Cause
    {
        $user = User::first() ?? User::create([
            'name' => 'U', 'email' => 'u@example.com', 'rotation_order' => 1, 'password' => 'x',
        ]);
        $cause = Cause::create(['name' => $name]);
        for ($i = 0; $i < $days; $i++) {
            Pick::create([
                'date' => today()->subDays(Pick::count() + 1)->toDateString(),
                'user_id' => $user->id,
                'cause_id' => $cause->id,
            ]);
        }

        return $cause;
    }

    public function test_no_banner_at_or_below_default_threshold(): void
    {
        Setup::create(['date_from' => '2000-01-01', 'amount_per_day' => 5]);
        $this->causeWithEarnings('Small', 10); // £50 exactly: not "above"

        $this->get('/stats')->assertOk()->assertDontSee('ready for');
    }

    public function test_single_cause_banner_and_table(): void
    {
        Setup::create(['date_from' => '2000-01-01', 'amount_per_day' => 5]);
        $this->causeWithEarnings('Big', 11); // £55

        $this->get('/stats')->assertOk()
            ->assertSee('Big is ready for a new donation')
            ->assertSee('£55.00');
    }

    public function test_multiple_causes_banner_and_threshold_setting(): void
    {
        Setup::create(['date_from' => '2000-01-01', 'amount_per_day' => 5]);
        $this->causeWithEarnings('A', 11);
        $this->causeWithEarnings('B', 12);
        $this->causeWithEarnings('C', 2);

        $this->get('/stats')->assertSee('2 causes are ready for donations');

        $this->post('/setup/threshold', ['donation_threshold' => 58])->assertRedirect();
        $this->assertSame(58.0, Setting::donationThreshold());
        $this->get('/stats')->assertSee('B is ready for a new donation');
    }
}

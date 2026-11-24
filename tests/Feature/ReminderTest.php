<?php

namespace Tests\Feature;

use App\Mail\PickReminder;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::setReminderEmailsEnabled(true); // off by default; most tests need it on
    }

    /**
     * Gives each user that many outstanding picks, on consecutive past days (so the backfill
     * adds nothing), using plain Y-m-d dates (see PersonTest::rawPick for why).
     */
    private function outstanding(array $perUser): void
    {
        $day = 1;
        foreach ($perUser as [$user, $count]) {
            for ($i = 0; $i < $count; $i++) {
                DB::table('picks')->insert([
                    'date' => today()->subDays($day++)->toDateString(),
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    public function test_only_people_at_or_over_the_default_threshold_of_five_are_emailed(): void
    {
        Mail::fake();
        $over  = $this->makeUser('Over', 1);
        $under = $this->makeUser('Under', 2);
        $exact = $this->makeUser('Exact', 3);
        $this->outstanding([[$over, 7], [$under, 4], [$exact, 5]]);

        $this->artisan('picks:send-reminders')->assertSuccessful();

        Mail::assertSent(PickReminder::class, 2);
        Mail::assertSent(PickReminder::class, fn ($m) => $m->hasTo($over->email) && $m->count === 7);
        Mail::assertSent(PickReminder::class, fn ($m) => $m->hasTo($exact->email) && $m->count === 5);
        Mail::assertNotSent(PickReminder::class, fn ($m) => $m->hasTo($under->email));
        $this->assertNotNull($over->fresh()->last_reminded_at);
        $this->assertNull($under->fresh()->last_reminded_at);
    }

    public function test_master_switch_is_off_by_default_and_blocks_everything(): void
    {
        Mail::fake();
        $user = $this->makeUser('Over', 1);
        $this->outstanding([[$user, 9]]);

        Setting::where('key', 'reminder_emails_enabled')->delete(); // fresh install: no row
        $this->assertFalse(Setting::reminderEmailsEnabled());
        $this->artisan('picks:send-reminders')->expectsOutputToContain('switched off')->assertSuccessful();
        Mail::assertNothingSent();
        $this->assertNull($user->fresh()->last_reminded_at);

        Setting::setReminderEmailsEnabled(false);
        $this->artisan('picks:send-reminders')->assertSuccessful();
        Mail::assertNothingSent();

        Setting::setReminderEmailsEnabled(true);
        $this->artisan('picks:send-reminders')->assertSuccessful();
        Mail::assertSent(PickReminder::class, 1);
    }

    public function test_only_the_picker_is_emailed_never_others(): void
    {
        Mail::fake();
        $over  = $this->makeUser('Over', 1);
        $other = $this->makeUser('Other', 2);
        $this->outstanding([[$over, 6], [$other, 1]]);

        $this->artisan('picks:send-reminders');

        Mail::assertSent(PickReminder::class, fn ($m) => $m->hasTo($over->email)
            && !$m->hasTo($other->email) && !$m->hasCc($other->email) && !$m->hasBcc($other->email));
    }

    public function test_at_most_one_reminder_a_week(): void
    {
        Mail::fake();
        $user = $this->makeUser('Over', 1);
        $this->outstanding([[$user, 6]]);

        $this->artisan('picks:send-reminders');
        $this->artisan('picks:send-reminders');
        Mail::assertSent(PickReminder::class, 1);

        $this->travel(6)->days();
        $this->artisan('picks:send-reminders');
        Mail::assertSent(PickReminder::class, 1);

        $this->travel(1)->days(); // 7 days after the first
        $this->artisan('picks:send-reminders');
        Mail::assertSent(PickReminder::class, 2);
    }

    public function test_opted_out_people_are_not_emailed(): void
    {
        Mail::fake();
        $user = $this->makeUser('Over', 1);
        $user->update(['reminders_enabled' => false]);
        $this->outstanding([[$user, 9]]);

        $this->artisan('picks:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_threshold_setting_is_respected_and_zero_turns_reminders_off(): void
    {
        Mail::fake();
        $user = $this->makeUser('Over', 1);
        $this->outstanding([[$user, 6]]);

        Setting::setReminderThreshold(7);
        $this->artisan('picks:send-reminders');
        Mail::assertNothingSent();

        Setting::setReminderThreshold(0);
        $this->artisan('picks:send-reminders');
        Mail::assertNothingSent();

        Setting::setReminderThreshold(6);
        $this->artisan('picks:send-reminders');
        Mail::assertSent(PickReminder::class, 1);
    }

    public function test_a_failed_send_is_not_recorded_so_it_is_retried(): void
    {
        $user = $this->makeUser('Over', 1);
        $this->outstanding([[$user, 6]]);

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->artisan('picks:send-reminders')->assertFailed();
        $this->assertNull($user->fresh()->last_reminded_at);
    }

    public function test_email_has_signed_unsubscribe_link_and_site_link(): void
    {
        $user = $this->makeUser('Over', 1);
        $html = (new PickReminder($user, 6))->render();

        $this->assertStringContainsString('Hi Over', $html);
        $this->assertStringContainsString('Stop sending me reminders', $html);
        $this->assertMatchesRegularExpression(
            '#reminders/unsubscribe/' . $user->id . '\?signature=#',
            html_entity_decode($html)
        );
        $this->assertStringContainsString(route('picks.today'), $html);
    }

    public function test_unsubscribe_link_works_without_choosing_a_person_and_needs_confirming(): void
    {
        $user = $this->makeUser('Over', 1);
        $this->makeUser('Other', 2); // so the "who are you?" gate would otherwise apply

        $url = URL::signedRoute('reminders.unsubscribe', ['user' => $user->id]);

        // GET only asks (mail scanners prefetch links) — it must not change anything.
        $this->get($url)->assertOk()->assertSee('Stop sending pick reminder emails');
        $this->assertTrue($user->fresh()->reminders_enabled);

        $this->post($url)->assertOk()->assertSee("won't get any more pick reminders", false);
        $this->assertFalse($user->fresh()->reminders_enabled);
    }

    public function test_unsubscribe_rejects_unsigned_and_tampered_links(): void
    {
        $user  = $this->makeUser('Over', 1);
        $other = $this->makeUser('Other', 2);

        $this->get(route('reminders.unsubscribe', $user))->assertForbidden();
        $this->post(route('reminders.unsubscribe.store', $user))->assertForbidden();

        // A signature for one person can't be reused on another.
        $signed = URL::signedRoute('reminders.unsubscribe', ['user' => $user->id]);
        $this->post(str_replace("/{$user->id}?", "/{$other->id}?", $signed))->assertForbidden();
        $this->assertTrue($other->fresh()->reminders_enabled);
    }

    public function test_setup_page_saves_the_threshold_and_user_edit_toggles_reminders(): void
    {
        $me = $this->makeUser('Me', 1);
        $this->actingAsPerson($me);

        // Fresh install: switch off (unticked), threshold defaults to 5.
        Setting::where('key', 'reminder_emails_enabled')->delete();
        $html = $this->get('/setup')->assertOk()->assertSee('Enable reminder emails')
            ->assertSee('value="5"', false)->getContent();
        $this->assertDoesNotMatchRegularExpression('/id="reminder_emails_enabled"[^>]*checked/s', $html);

        $this->post('/setup/reminders', ['reminder_emails_enabled' => '1', 'reminder_threshold' => 8])
            ->assertRedirect(route('setup.index'));
        $this->assertSame(8, Setting::reminderThreshold());
        $this->assertTrue(Setting::reminderEmailsEnabled());

        $this->post('/setup/reminders', ['reminder_emails_enabled' => '1', 'reminder_threshold' => -1])
            ->assertSessionHasErrors('reminder_threshold');
        $this->assertSame(8, Setting::reminderThreshold());

        $this->post('/setup/reminders', ['reminder_emails_enabled' => '0', 'reminder_threshold' => 8]);
        $this->assertFalse(Setting::reminderEmailsEnabled());

        $this->put(route('users.update', $me), [
            'name' => 'Me', 'email' => $me->email, 'rotation_order' => 1, 'reminders_enabled' => '0',
        ])->assertRedirect();
        $this->assertFalse($me->fresh()->reminders_enabled);
        $this->get(route('users.edit', $me))->assertOk()->assertDontSee('checked');
    }

    public function test_reminder_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('picks:send-reminders')->assertSuccessful();
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\CurrentPerson;
use App\Models\Pick;
use App\Models\Setup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_device_is_prompted_to_choose_a_person_and_returned_afterwards(): void
    {
        $alice = $this->makeUser('Alice', 1);
        $this->makeUser('Bob', 2);

        $this->get('/causes')->assertRedirect(route('person.choose'));
        $this->get(route('person.choose'))->assertOk()->assertSee('Alice')->assertSee('Bob');

        // Choosing from the prompt sends them where they were heading, with the cookie set.
        $this->from(route('person.choose'))
            ->post(route('person.store'), ['user_id' => $alice->id])
            ->assertRedirect('http://localhost/causes')
            ->assertCookie(CurrentPerson::COOKIE, (string) $alice->id);
    }

    public function test_unknown_cookie_is_treated_as_no_person(): void
    {
        $this->makeUser('Alice', 1);

        $this->withCookie(CurrentPerson::COOKIE, '9999')->get('/causes')
            ->assertRedirect(route('person.choose'));
    }

    public function test_dropdown_shows_everyone_with_current_person_selected_and_changing_stays_on_page(): void
    {
        $alice = $this->makeUser('Alice', 1);
        $bob   = $this->makeUser('Bob', 2);

        $html = $this->actingAsPerson($bob)->get('/causes')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<option value="' . $bob->id . '"\s+selected>Bob/', $html);
        $this->assertStringContainsString('Alice</option>', $html);

        $this->from('/causes')->post(route('person.store'), ['user_id' => $alice->id])
            ->assertRedirect('/causes')
            ->assertCookie(CurrentPerson::COOKIE, (string) $alice->id);
    }

    public function test_all_features_available_to_any_person(): void
    {
        $alice = $this->makeUser('Alice', 1);
        $this->actingAsPerson($alice);

        foreach (['/causes', '/donations', '/stats', '/setup', '/users'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    /**
     * Insert a pick with a plain Y-m-d date. (Via the model, SQLite stores 'Y-m-d 00:00:00' and the
     * app's `where('date', 'Y-m-d')` lookups then miss it; MySQL's date column has no such problem.)
     */
    private function rawPick(int $daysAgo, User $user): void
    {
        DB::table('picks')->insert([
            'date' => today()->subDays($daysAgo)->toDateString(),
            'user_id' => $user->id,
        ]);
    }

    /** Alice, Bob, Alice outstanding on the last three days; today (no cause yet) is Bob's turn. */
    private function seedOutstanding(): array
    {
        Setup::create(['date_from' => '2000-01-01', 'amount_per_day' => 5]);
        $alice = $this->makeUser('Alice', 1);
        $bob   = $this->makeUser('bob', 2);
        foreach ([[3, $alice], [2, $bob], [1, $alice], [0, $bob]] as [$daysAgo, $user]) {
            $this->rawPick($daysAgo, $user);
        }

        return [$alice, $bob];
    }

    public function test_my_outstanding_in_full_and_others_summarised(): void
    {
        [$alice, $bob] = $this->seedOutstanding();

        $this->actingAsPerson($alice)->get('/')->assertOk()
            ->assertSee('My outstanding picks')
            ->assertSee("Others' picks", false)
            ->assertSee(today()->subDays(3)->format('j M Y'))
            ->assertDontSee(today()->subDays(2)->format('j M Y')) // Bob's date isn't listed individually
            ->assertSeeInOrder(['bob', '<strong>1</strong>'], false);
    }

    public function test_sections_omitted_when_empty(): void
    {
        [$alice, $bob] = $this->seedOutstanding();

        // Bob only has one outstanding; give him a person with none to check omission both ways.
        $carol = $this->makeUser('Carol', 3);
        $this->actingAsPerson($carol)->get('/')->assertOk()
            ->assertDontSee('My outstanding picks')
            ->assertSee("Others' picks", false)
            ->assertDontSee('Carol</span>', false); // no outstanding → not in the summary

        // Alice and Bob have none once everything is chosen.
        Pick::whereNull('cause_id')->delete();
        $this->actingAsPerson($alice)->get('/')->assertOk()
            ->assertDontSee('My outstanding picks')
            ->assertDontSee("Others' picks", false);
    }

    public function test_others_listed_alphabetically_ignoring_case_with_counts(): void
    {
        Setup::create(['date_from' => '2000-01-01', 'amount_per_day' => 5]);
        $me = $this->makeUser('Me', 1);
        $zed = $this->makeUser('Zed', 2);
        $amy = $this->makeUser('amy', 3);
        foreach ([[4, $zed], [3, $amy], [2, $zed], [1, $amy]] as [$d, $u]) {
            $this->rawPick($d, $u);
        }

        $this->actingAsPerson($me)->get('/')->assertOk()
            ->assertSeeInOrder(['amy', '<strong>2</strong>', 'Zed', '<strong>2</strong>'], false);
    }

    public function test_choose_area_disabled_unless_it_is_your_turn(): void
    {
        [$alice, $bob] = $this->seedOutstanding(); // today's pick → rotation says Bob

        $this->actingAsPerson($alice)->get('/')->assertOk()
            ->assertSee('not-your-turn')
            ->assertSee('<fieldset disabled', false)
            ->assertSee("only they can choose");

        $this->actingAsPerson($bob)->get('/')->assertOk()
            ->assertDontSee('<fieldset disabled', false)
            ->assertDontSee('only they can choose');
    }

    public function test_choose_area_for_a_selected_past_date_follows_that_dates_picker(): void
    {
        [$alice, $bob] = $this->seedOutstanding();
        $aliceDay = today()->subDays(3)->toDateString();

        $this->actingAsPerson($alice)->get(route('picks.date', $aliceDay))->assertOk()
            ->assertDontSee('<fieldset disabled', false);
        $this->actingAsPerson($bob)->get(route('picks.date', $aliceDay))->assertOk()
            ->assertSee('<fieldset disabled', false);
    }
}

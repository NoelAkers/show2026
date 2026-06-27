<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function leaderboardAdmin(): User
{
    return User::factory()->admin()->create();
}

it('leaderboard shows all exhibitors with points sorted by score', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $first = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Adams', 'full_name' => 'Alice Adams', 'sort_name' => 'Adams, Alice']);
    $second = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Brown', 'full_name' => 'Bob Brown', 'sort_name' => 'Brown, Bob']);

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $first->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $second->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts

    $this->actingAs(leaderboardAdmin())
        ->get(route('admin.trophies.leaderboard', $trophy))
        ->assertOk()
        ->assertSee('Alice Adams')
        ->assertSee('Bob Brown');
});

it('leaderboard marks ineligible exhibitors when trophy has restrictions', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'resident']);

    $resident = Exhibitor::factory()->resident()->create(['first_name' => 'Carol', 'last_name' => 'Cook', 'full_name' => 'Carol Cook', 'sort_name' => 'Cook, Carol']);
    $nonResident = Exhibitor::factory()->nonResident()->create(['first_name' => 'Dave', 'last_name' => 'Dean', 'full_name' => 'Dave Dean', 'sort_name' => 'Dean, Dave']);

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $nonResident->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $resident->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts — ineligible
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts — eligible

    $this->actingAs(leaderboardAdmin())
        ->get(route('admin.trophies.leaderboard', $trophy))
        ->assertOk()
        ->assertSee('Dave Dean')   // shown for cross-reference
        ->assertSee('Carol Cook')
        ->assertSee('Ineligible'); // badge shown for non-resident
});

it('leaderboard shows restriction badges for the trophy', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'novice']);

    $this->actingAs(leaderboardAdmin())
        ->get(route('admin.trophies.leaderboard', $trophy))
        ->assertOk()
        ->assertSee('Novices only');
});

it('leaderboard shows empty state when no points scored', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class->id);
    Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs(leaderboardAdmin())
        ->get(route('admin.trophies.leaderboard', $trophy))
        ->assertOk()
        ->assertSee('No points scored yet');
});

it('leaderboard is limited to 10 exhibitors', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    Exhibitor::factory()->count(12)->create()->each(function (Exhibitor $exhibitor) use ($class) {
        $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
        Result::factory()->create(['entry_id' => $entry->id, 'placement' => '3rd']);
    });

    $rows = $trophy->leaderboard();

    expect($rows)->toHaveCount(10);
});

it('guest is redirected from trophy leaderboard', function () {
    $trophy = Trophy::factory()->create();

    $this->get(route('admin.trophies.leaderboard', $trophy))
        ->assertRedirect(route('login'));
});

it('judge receives 403 on trophy leaderboard', function () {
    $trophy = Trophy::factory()->create();

    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.trophies.leaderboard', $trophy))
        ->assertForbidden();
});

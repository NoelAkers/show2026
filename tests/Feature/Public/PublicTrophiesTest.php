<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\Trophy;

test('public trophies page is accessible without login', function () {
    $this->get(route('public.trophies'))->assertSuccessful();
});

test('trophy name and winner name are shown', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1]);
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Jones', 'full_name' => 'Alice Jones', 'sort_name' => 'Jones, Alice']);
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    $trophy = Trophy::factory()->create(['name' => 'Best in Show', 'is_points_based' => true]);
    $trophy->showClasses()->attach($class);

    $response = $this->get(route('public.trophies'));

    $response->assertSee('Best in Show');
    $response->assertSee('Alice Jones');
});

test('to be announced shown when no results have been entered', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1]);

    $trophy = Trophy::factory()->create(['name' => 'Best in Show', 'is_points_based' => true]);
    $trophy->showClasses()->attach($class);

    $response = $this->get(route('public.trophies'));

    $response->assertSee('To be announced.');
});

test('all tied winners are listed when there is a tie', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1]);

    $exhibitorA = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Alpha', 'full_name' => 'Alice Alpha', 'sort_name' => 'Alpha, Alice']);
    $exhibitorB = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Beta', 'full_name' => 'Bob Beta', 'sort_name' => 'Beta, Bob']);

    $entryA = Entry::factory()->for($class, 'showClass')->for($exhibitorA)->create();
    $entryB = Entry::factory()->for($class, 'showClass')->for($exhibitorB)->create();
    Result::factory()->for($entryA)->create(['placement' => '1st']);
    Result::factory()->for($entryB)->create(['placement' => '1st']);

    $trophy = Trophy::factory()->create(['name' => 'Best in Show', 'is_points_based' => true]);
    $trophy->showClasses()->attach($class);

    $response = $this->get(route('public.trophies'));

    $response->assertSee('Alice Alpha');
    $response->assertSee('Bob Beta');
});

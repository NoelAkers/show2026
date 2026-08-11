<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public results page is accessible without login', function () {
    $this->get(route('public.results'))->assertSuccessful();
});

test('placement labels are shown per result', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1]);
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith', 'full_name' => 'Jane Smith', 'sort_name' => 'Smith, Jane']);
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    $response = $this->get(route('public.results'));

    $response->assertSee('Jane Smith');
    $response->assertSee('1st Place');
});

test('class name is preceded by class ID', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1, 'name' => 'Roses']);

    $response = $this->get(route('public.results'));

    $response->assertSee($class->id.'. Roses');
});

test('points totals are not present in the response', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1]);
    $entry = Entry::factory()->for($class, 'showClass')->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    $response = $this->get(route('public.results'));

    $response->assertDontSee('points');
});

test('class with no placed results shows results pending', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    ShowClass::factory()->for($section)->create(['sort_order' => 1]);

    $response = $this->get(route('public.results'));

    $response->assertSee('Results pending.');
});

test('exhibitor email phone and address are not present in the response', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    $class = ShowClass::factory()->for($section)->create(['sort_order' => 1]);
    $entry = Entry::factory()->for($class, 'showClass')->create();
    Result::factory()->for($entry)->create(['placement' => '2nd']);

    $response = $this->get(route('public.results'));

    $response->assertDontSee('email');
    $response->assertDontSee('phone');
    $response->assertDontSee('address');
});

<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('dashboard is accessible to admin', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSuccessful();
});

test('judge accessing dashboard is redirected to judge sections', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('judge.sections.index'));
});

test('correct section count is displayed', function () {
    ShowSection::factory()->count(3)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('3');
});

test('correct class count is displayed', function () {
    $section = ShowSection::factory()->create();
    ShowClass::factory()->for($section)->count(4)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('4');
});

test('correct exhibitor count with adult and junior split is displayed', function () {
    Exhibitor::factory()->adult()->count(5)->create();
    Exhibitor::factory()->junior()->count(2)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('5 adult')
        ->assertSee('2 junior');
});

test('correct entry count is displayed', function () {
    Entry::factory()->count(6)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('6');
});

test('correct results entered and outstanding counts are displayed', function () {
    $entries = Entry::factory()->count(3)->create();
    Result::factory()->for($entries->first())->create(['placement' => '1st']);
    Result::factory()->for($entries->get(1))->create(['placement' => null]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('1')
        ->assertSee('2 outstanding');
});

test('correct paid and unpaid exhibitor count is displayed', function () {
    Exhibitor::factory()->adult()->count(3)->create(['has_paid' => true]);
    Exhibitor::factory()->adult()->count(2)->create(['has_paid' => false]);
    Exhibitor::factory()->junior()->count(4)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('3')
        ->assertSee('2 unpaid');
});

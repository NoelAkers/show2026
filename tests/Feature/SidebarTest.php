<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sidebar header shows Menu instead of Laravel Starter Kit', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee('Menu')
        ->assertDontSee('Laravel Starter Kit');
});

it('admin sidebar shows Pre-show group with Stewards link', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee('Pre-show')
        ->assertSee('Sections')
        ->assertSee('Users')
        ->assertSee('Judges')
        ->assertSee('Stewards')
        ->assertSee('Trophies')
        ->assertSee(route('admin.stewards.index'));
});

it('admin sidebar shows Entries group', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee('Entries')
        ->assertSee('Exhibitors')
        ->assertSee(route('admin.exhibitors.index'));
});

it('admin sidebar shows Results group with Leaderboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee('Results')
        ->assertSee('Leaderboard')
        ->assertSee(route('admin.leaderboard'));
});

it('admin sidebar shows Dashboard item', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSee('Dashboard')
        ->assertSee(route('dashboard'));
});

it('judge sidebar shows My Sections group with Sections link', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs($judge)
        ->get(route('judge.sections.index'))
        ->assertSee('My Sections')
        ->assertSee('Sections')
        ->assertSee(route('judge.sections.index'))
        ->assertDontSee('Pre-show')
        ->assertDontSee('Exhibitors');
});

it('steward sidebar shows My Sections group with Sections link', function () {
    $steward = User::factory()->steward()->create();

    $this->actingAs($steward)
        ->get(route('steward.sections.index'))
        ->assertSee('My Sections')
        ->assertSee('Sections')
        ->assertSee(route('steward.sections.index'))
        ->assertDontSee('Pre-show')
        ->assertDontSee('Exhibitors');
});

it('helper sidebar shows Exhibitors link', function () {
    $helper = User::factory()->helper()->create();

    $this->actingAs($helper)
        ->get(route('dashboard'))
        ->assertSee('Exhibitors')
        ->assertSee(route('helper.exhibitors.index'))
        ->assertDontSee('Pre-show')
        ->assertDontSee('Stewards');
});

it('exhibitor sidebar shows My Entries link', function () {
    config(['show.self_entry_open' => true]);

    $exhibitor = User::factory()->withExhibitor()->create();

    $this->actingAs($exhibitor)
        ->get(route('exhibitor.dashboard'))
        ->assertSee('My Entries')
        ->assertDontSee('Pre-show')
        ->assertDontSee('Stewards');
});

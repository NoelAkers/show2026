<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('steward can view their assigned trophies index', function () {
    $steward = User::factory()->steward()->create();
    Trophy::factory()->judgeAwarded()->create(['name' => 'Best in Show', 'steward_id' => $steward->id]);

    $this->actingAs($steward)
        ->get(route('steward.trophies.index'))
        ->assertOk()
        ->assertSee('Best in Show');
});

it('steward does not see trophies assigned to other stewards', function () {
    $steward = User::factory()->steward()->create();
    $otherSteward = User::factory()->steward()->create();
    Trophy::factory()->judgeAwarded()->create(['name' => 'Other Trophy', 'steward_id' => $otherSteward->id]);

    $this->actingAs($steward)
        ->get(route('steward.trophies.index'))
        ->assertOk()
        ->assertDontSee('Other Trophy');
});

it('steward can view a trophy assigned to them', function () {
    $steward = User::factory()->steward()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['name' => 'Special Award', 'steward_id' => $steward->id]);

    $this->actingAs($steward)
        ->get(route('steward.trophies.show', $trophy))
        ->assertOk()
        ->assertSee('Special Award');
});

it('steward gets 403 viewing a trophy not assigned to them', function () {
    $steward = User::factory()->steward()->create();
    $otherSteward = User::factory()->steward()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['steward_id' => $otherSteward->id]);

    $this->actingAs($steward)
        ->get(route('steward.trophies.show', $trophy))
        ->assertForbidden();
});

it('steward can save a winning entry for their trophy', function () {
    $steward = User::factory()->steward()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['steward_id' => $steward->id]);
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    Livewire::actingAs($steward)
        ->test(App\Livewire\Steward\Results\Trophy::class, ['trophy' => $trophy])
        ->set('winningEntryId', $entry->id)
        ->call('save');

    expect($trophy->fresh()->winning_entry_id)->toBe($entry->id);
});

it('steward cannot access winning entry form for a trophy not assigned to them', function () {
    $steward = User::factory()->steward()->create();
    $otherSteward = User::factory()->steward()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['steward_id' => $otherSteward->id]);

    Livewire::actingAs($steward)
        ->test(App\Livewire\Steward\Results\Trophy::class, ['trophy' => $trophy])
        ->assertForbidden();
});

it('steward trophy index shows current winner when set', function () {
    $steward = User::factory()->steward()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Trophy::factory()->judgeAwarded()->create([
        'name' => 'Grand Award',
        'steward_id' => $steward->id,
        'winning_entry_id' => $entry->id,
    ]);

    $this->actingAs($steward)
        ->get(route('steward.trophies.index'))
        ->assertOk()
        ->assertSee('Bob Jones');
});

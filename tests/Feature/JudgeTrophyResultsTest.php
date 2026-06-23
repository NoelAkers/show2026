<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('judge can view their assigned trophies index', function () {
    $judge = User::factory()->judge()->create();
    Trophy::factory()->judgeAwarded()->create(['name' => 'Best in Show', 'judge_id' => $judge->id]);

    $this->actingAs($judge)
        ->get(route('judge.trophies.index'))
        ->assertOk()
        ->assertSee('Best in Show');
});

it('judge does not see trophies assigned to other judges', function () {
    $judge = User::factory()->judge()->create();
    $otherJudge = User::factory()->judge()->create();
    Trophy::factory()->judgeAwarded()->create(['name' => 'Other Trophy', 'judge_id' => $otherJudge->id]);

    $this->actingAs($judge)
        ->get(route('judge.trophies.index'))
        ->assertOk()
        ->assertDontSee('Other Trophy');
});

it('judge can view a trophy assigned to them', function () {
    $judge = User::factory()->judge()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['name' => 'Special Award', 'judge_id' => $judge->id]);

    $this->actingAs($judge)
        ->get(route('judge.trophies.show', $trophy))
        ->assertOk()
        ->assertSee('Special Award');
});

it('judge gets 403 viewing a trophy not assigned to them', function () {
    $judge = User::factory()->judge()->create();
    $otherJudge = User::factory()->judge()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $otherJudge->id]);

    $this->actingAs($judge)
        ->get(route('judge.trophies.show', $trophy))
        ->assertForbidden();
});

it('judge can save a winning entry for their trophy by entry number', function () {
    $judge = User::factory()->judge()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $judge->id]);
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    Livewire::actingAs($judge)
        ->test(App\Livewire\Judge\Results\Trophy::class, ['trophy' => $trophy])
        ->set('winningEntryNumber', $entry->formatted_entry_number)
        ->call('save');

    expect($trophy->fresh()->winning_entry_id)->toBe($entry->id);
});

it('judge sees an error when entering an invalid entry number', function () {
    $judge = User::factory()->judge()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $judge->id]);

    Livewire::actingAs($judge)
        ->test(App\Livewire\Judge\Results\Trophy::class, ['trophy' => $trophy])
        ->set('winningEntryNumber', '999')
        ->call('save')
        ->assertHasErrors(['winningEntryNumber']);

    expect($trophy->fresh()->winning_entry_id)->toBeNull();
});

it('judge can clear the winning entry by submitting an empty entry number', function () {
    $judge = User::factory()->judge()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $judge->id, 'winning_entry_id' => $entry->id]);

    Livewire::actingAs($judge)
        ->test(App\Livewire\Judge\Results\Trophy::class, ['trophy' => $trophy])
        ->set('winningEntryNumber', '')
        ->call('save');

    expect($trophy->fresh()->winning_entry_id)->toBeNull();
});

it('judge cannot access winning entry form for a trophy not assigned to them', function () {
    $judge = User::factory()->judge()->create();
    $otherJudge = User::factory()->judge()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $otherJudge->id]);

    Livewire::actingAs($judge)
        ->test(App\Livewire\Judge\Results\Trophy::class, ['trophy' => $trophy])
        ->assertForbidden();
});

it('judge trophy index shows winning entry number when set', function () {
    $judge = User::factory()->judge()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Trophy::factory()->judgeAwarded()->create([
        'name' => 'Grand Award',
        'judge_id' => $judge->id,
        'winning_entry_id' => $entry->id,
    ]);

    $this->actingAs($judge)
        ->get(route('judge.trophies.index'))
        ->assertOk()
        ->assertSee($entry->formatted_entry_number);
});

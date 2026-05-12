<?php

use App\Livewire\Admin\Exhibitors\EntryManager;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->exhibitor = Exhibitor::factory()->create();
    $this->section = ShowSection::factory()->create(['sort_order' => 1]);
    $this->class = ShowClass::factory()->create([
        'show_section_id' => $this->section->id,
        'max_entries_per_exhibitor' => 3,
    ]);
});

it('mounts with quantities reflecting existing entries', function () {
    Entry::factory()->count(2)->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->assertSet("quantities.{$this->class->id}", 2);
});

it('increment increases quantity by one', function () {
    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->assertSet("quantities.{$this->class->id}", 0)
        ->call('increment', $this->class->id)
        ->assertSet("quantities.{$this->class->id}", 1);
});

it('increment does not exceed max_entries_per_exhibitor', function () {
    Entry::factory()->count(3)->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->assertSet("quantities.{$this->class->id}", 3)
        ->call('increment', $this->class->id)
        ->assertSet("quantities.{$this->class->id}", 3);
});

it('decrement decreases quantity by one', function () {
    Entry::factory()->count(2)->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->assertSet("quantities.{$this->class->id}", 2)
        ->call('decrement', $this->class->id)
        ->assertSet("quantities.{$this->class->id}", 1);
});

it('decrement cannot go below locked entries with results', function () {
    $entry = Entry::factory()->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);
    Result::factory()->create(['entry_id' => $entry->id]);

    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->assertSet("quantities.{$this->class->id}", 1)
        ->call('decrement', $this->class->id)
        ->assertSet("quantities.{$this->class->id}", 1);
});

it('save creates new entries when quantity is increased', function () {
    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->call('increment', $this->class->id)
        ->call('increment', $this->class->id)
        ->call('save');

    expect(
        Entry::where('exhibitor_id', $this->exhibitor->id)
            ->where('show_class_id', $this->class->id)
            ->count()
    )->toBe(2);
});

it('save removes entries without results when quantity is decreased', function () {
    $entries = Entry::factory()->count(3)->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);

    // Lock one entry with a result
    Result::factory()->create(['entry_id' => $entries->first()->id]);

    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->call('decrement', $this->class->id)
        ->call('save');

    expect(
        Entry::where('exhibitor_id', $this->exhibitor->id)
            ->where('show_class_id', $this->class->id)
            ->count()
    )->toBe(2);
});

it('save does not delete entries that have results', function () {
    $entry = Entry::factory()->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);
    Result::factory()->create(['entry_id' => $entry->id]);

    // Even if we try to set quantity to 0, locked entry must remain
    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->set("quantities.{$this->class->id}", 0)
        ->call('save');

    expect(Entry::where('id', $entry->id)->exists())->toBeTrue();
});

it('save sets info message when nothing changed', function () {
    Entry::factory()->create([
        'exhibitor_id' => $this->exhibitor->id,
        'show_class_id' => $this->class->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->call('save')
        ->assertSet('statusMessage', 'No changes to save.')
        ->assertSet('statusIsSuccess', false);
});

it('save sets success message when entries are added', function () {
    Livewire::actingAs($this->admin)
        ->test(EntryManager::class, ['exhibitor' => $this->exhibitor])
        ->call('increment', $this->class->id)
        ->call('save')
        ->assertSet('statusIsSuccess', true)
        ->assertSet('statusMessage', '1 entry added.');
});

it('renders the accordion page for admins', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.exhibitors.add-entry', $this->exhibitor))
        ->assertOk()
        ->assertSeeLivewire(EntryManager::class);
});

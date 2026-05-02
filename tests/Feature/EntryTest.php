<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory creates a valid Entry with an auto-assigned entry_number', function () {
    $entry = Entry::factory()->create();

    expect($entry->exists)->toBeTrue();
    expect($entry->entry_number)->toBeInt()->toBeGreaterThan(0);
});

it('entry_number is globally unique', function () {
    $entry1 = Entry::factory()->create();
    $entry2 = Entry::factory()->create();

    expect($entry1->entry_number)->not->toBe($entry2->entry_number);
});

it('two entries in sequence get consecutive numbers', function () {
    $entry1 = Entry::factory()->create();
    $entry2 = Entry::factory()->create();

    expect($entry2->entry_number)->toBe($entry1->entry_number + 1);
});

it('deleting a ShowClass with entries is prevented', function () {
    $class = ShowClass::factory()->create();
    Entry::factory()->create(['show_class_id' => $class->id]);

    expect(fn () => $class->delete())
        ->toThrow(QueryException::class);
});

it('feeOwedPence returns 0 for juniors regardless of entry count', function () {
    $exhibitor = Exhibitor::factory()->junior()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    Entry::factory()->count(5)->create(['exhibitor_id' => $exhibitor->id, 'show_class_id' => $class->id]);

    expect($exhibitor->feeOwedPence())->toBe(0);
    expect($exhibitor->chargeableEntries())->toBe(0);
});

it('feeOwedPence charges for entries 1 to 10 for adults', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    Entry::factory()->count(5)->create(['exhibitor_id' => $exhibitor->id, 'show_class_id' => $class->id]);

    expect($exhibitor->feeOwedPence())->toBe(5 * config('show.entry_fee_pence'));
});

it('feeOwedPence caps at 10 chargeable entries for adults with 11 or more entries', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    Entry::factory()->count(12)->create(['exhibitor_id' => $exhibitor->id, 'show_class_id' => $class->id]);

    expect($exhibitor->chargeableEntries())->toBe(10);
    expect($exhibitor->feeOwedPence())->toBe(10 * config('show.entry_fee_pence'));
});

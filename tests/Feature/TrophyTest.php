<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\Trophy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory creates a valid Trophy', function () {
    $trophy = Trophy::factory()->create();

    expect($trophy->exists)->toBeTrue();
    expect($trophy->name)->toBeString()->not->toBeEmpty();
});

it('winners returns empty collection when no classes assigned', function () {
    $trophy = Trophy::factory()->create();

    expect($trophy->winners())->toBeEmpty();
});

it('winners returns empty collection when assigned classes have no results', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class->id);

    Entry::factory()->create(['show_class_id' => $class->id]);

    expect($trophy->winners())->toBeEmpty();
});

it('winners returns the exhibitor with the most points', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $winner = Exhibitor::factory()->create();
    $other = Exhibitor::factory()->create();

    $entryWinner = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $winner->id]);
    $entryOther = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $other->id]);

    Result::factory()->create(['entry_id' => $entryWinner->id, 'placement' => '1st']);
    Result::factory()->create(['entry_id' => $entryOther->id, 'placement' => '2nd']);

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($winner->id);
    expect($winners->first()['points'])->toBe(3);
});

it('winners returns all exhibitors when there is a points tie', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $exhibitor1 = Exhibitor::factory()->create();
    $exhibitor2 = Exhibitor::factory()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor2->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '1st']);

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(2);
});

it('a ShowClass can be assigned to multiple trophies', function () {
    $class = ShowClass::factory()->create();
    $trophy1 = Trophy::factory()->create();
    $trophy2 = Trophy::factory()->create();

    $trophy1->showClasses()->attach($class->id);
    $trophy2->showClasses()->attach($class->id);

    expect($trophy1->showClasses()->count())->toBe(1);
    expect($trophy2->showClasses()->count())->toBe(1);
});

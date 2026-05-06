<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\Trophy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('winner is the exhibitor with the most points across assigned classes', function () {
    $trophy = Trophy::factory()->create();
    $class1 = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $class2 = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach([$class1->id, $class2->id]);

    $leader = Exhibitor::factory()->create();
    $other = Exhibitor::factory()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class1->id, 'exhibitor_id' => $leader->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class2->id, 'exhibitor_id' => $leader->id]);
    $entry3 = Entry::factory()->create(['show_class_id' => $class1->id, 'exhibitor_id' => $other->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 3 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts
    Result::factory()->create(['entry_id' => $entry3->id, 'placement' => '1st']); // 3 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($leader->id);
    expect($winners->first()['points'])->toBe(5);
});

it('all tied exhibitors are returned when points are equal', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $exhibitor1 = Exhibitor::factory()->create();
    $exhibitor2 = Exhibitor::factory()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor2->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '2nd']);
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']);

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(2);
    $ids = $winners->pluck('exhibitor.id')->toArray();
    expect($ids)->toContain($exhibitor1->id)->toContain($exhibitor2->id);
});

it('adding a new result updates the winner output', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $exhibitor1 = Exhibitor::factory()->create();
    $exhibitor2 = Exhibitor::factory()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor2->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '2nd']); // 2 pts

    expect($trophy->winners()->first()['exhibitor']->id)->toBe($exhibitor1->id);

    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '1st']); // 3 pts

    expect($trophy->winners()->first()['exhibitor']->id)->toBe($exhibitor2->id);
});

it('trophy with no assigned classes returns empty winner list', function () {
    $trophy = Trophy::factory()->create();

    expect($trophy->winners())->toBeEmpty();
});

it('trophy with assigned classes but no results returns empty winner list', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class->id);

    Entry::factory()->create(['show_class_id' => $class->id]);

    expect($trophy->winners())->toBeEmpty();
});

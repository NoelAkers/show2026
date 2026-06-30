<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\Trophy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts
    Result::factory()->create(['entry_id' => $entry3->id, 'placement' => '1st']); // 4 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($leader->id);
    expect($winners->first()['points'])->toBe(6);
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

it('judge-awarded trophy with no winning entry returns empty winner list', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create();

    expect($trophy->winners())->toBeEmpty();
});

it('judge-awarded trophy returns the winning entry exhibitor as winner', function () {
    $exhibitor = Exhibitor::factory()->create();
    $class = ShowClass::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $trophy = Trophy::factory()->judgeAwarded()->create(['winning_entry_id' => $entry->id]);

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($exhibitor->id);
    expect($winners->first()['points'])->toBeNull();
});

it('resident restriction excludes non-residents from winners', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'resident']);

    $resident = Exhibitor::factory()->resident()->create();
    $nonResident = Exhibitor::factory()->nonResident()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $nonResident->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $resident->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($resident->id);
});

it('novice restriction excludes non-novices from winners', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'novice']);

    $novice = Exhibitor::factory()->novice()->create();
    $experienced = Exhibitor::factory()->notNovice()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $experienced->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $novice->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($novice->id);
});

it('junior restriction excludes adult exhibitors from winners', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'junior']);

    $junior = Exhibitor::factory()->junior()->create();
    $adult = Exhibitor::factory()->adult()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $adult->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $junior->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($junior->id);
});

it('combined restrictions require all criteria to be met', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert([
        ['trophy_id' => $trophy->id, 'restriction' => 'resident'],
        ['trophy_id' => $trophy->id, 'restriction' => 'novice'],
    ]);

    $residentNovice = Exhibitor::factory()->resident()->novice()->create();
    $residentExperienced = Exhibitor::factory()->resident()->notNovice()->create();
    $nonResidentNovice = Exhibitor::factory()->nonResident()->novice()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $residentExperienced->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $nonResidentNovice->id]);
    $entry3 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $residentNovice->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry3->id, 'placement' => '2nd']); // 2 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($residentNovice->id);
});

it('trophy with no restrictions considers all exhibitors', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $resident = Exhibitor::factory()->resident()->create();
    $nonResident = Exhibitor::factory()->nonResident()->create();

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $nonResident->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $resident->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 4 pts
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']); // 2 pts

    $winners = $trophy->winners();

    expect($winners)->toHaveCount(1);
    expect($winners->first()['exhibitor']->id)->toBe($nonResident->id);
});

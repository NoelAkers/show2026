<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view exhibitor detail page', function () {
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee('Alice Smith');
});

it('exhibitor detail shows entry list with class name, section name, entry number, and placement', function () {
    $section = ShowSection::factory()->create(['name' => 'Flowers']);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Best Rose']);
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '2nd']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee('Flowers')
        ->assertSee('Best Rose')
        ->assertSee((string) $entry->entry_number)
        ->assertSee('2nd Place');
});

it('exhibitor detail shows pending when no result entered', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee('Pending');
});

it('fee summary on exhibitor detail is correct', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create(['full_name' => 'Carol White', 'sort_name' => 'White, Carol']);
    Entry::factory()->count(3)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $expectedFee = number_format(3 * config('show.entry_fee_pence') / 100, 2);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee($expectedFee);
});

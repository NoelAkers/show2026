<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function entryAdmin(): User
{
    return User::factory()->admin()->create();
}

// --- Phase 6.1 + 6.2: Record entries & view entries per class ---

it('admin can view the class detail page', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Floral Arrangements']);

    $this->actingAs(entryAdmin())
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee('Floral Arrangements');
});

it('admin can record an entry linking an exhibitor to a class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(entryAdmin())
        ->post(route('admin.show-sections.show-classes.entries.store', [$section, $class]), [
            'exhibitor_id' => $exhibitor->id,
        ])
        ->assertRedirect(route('admin.show-sections.show-classes.show', [$section, $class]));

    $this->assertDatabaseHas('entries', [
        'show_class_id' => $class->id,
        'exhibitor_id' => $exhibitor->id,
    ]);
});

it('entry_number is auto-assigned and globally unique', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor1 = Exhibitor::factory()->create();
    $exhibitor2 = Exhibitor::factory()->create();

    $this->actingAs(entryAdmin())
        ->post(route('admin.show-sections.show-classes.entries.store', [$section, $class]), ['exhibitor_id' => $exhibitor1->id]);

    $this->actingAs(entryAdmin())
        ->post(route('admin.show-sections.show-classes.entries.store', [$section, $class]), ['exhibitor_id' => $exhibitor2->id]);

    $numbers = Entry::pluck('entry_number');
    expect($numbers->unique()->count())->toBe($numbers->count());
});

it('exhibitor cannot exceed max_entries_per_exhibitor for a class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'max_entries_per_exhibitor' => 2]);
    $exhibitor = Exhibitor::factory()->create();

    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(entryAdmin())
        ->post(route('admin.show-sections.show-classes.entries.store', [$section, $class]), [
            'exhibitor_id' => $exhibitor->id,
        ])
        ->assertSessionHasErrors('exhibitor_id');
});

it('entry appears in the class entry list after creation', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe', 'full_name' => 'Jane Doe', 'sort_name' => 'Doe, Jane']);

    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(entryAdmin())
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee('Jane Doe');
});

it('entry list shows entry number, exhibitor name, type, and result status', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create(['full_name' => 'John Smith', 'sort_name' => 'Smith, John']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(entryAdmin())
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee('John Smith')
        ->assertSee('Adult')
        ->assertSee('No placement')
        ->assertSee((string) $entry->entry_number);
});

it('result status shows placement label when result is entered', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Jane Doe', 'sort_name' => 'Doe, Jane']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '1st']);

    $this->actingAs(entryAdmin())
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee('1st Place');
});

// --- Phase 6.4: Delete entry ---

it('admin can delete an entry that has no result', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs(entryAdmin())
        ->delete(route('admin.show-sections.show-classes.entries.destroy', [$section, $class, $entry]))
        ->assertRedirect(route('admin.show-sections.show-classes.show', [$section, $class]));

    $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
});

it('admin cannot delete an entry that already has a result', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry->id]);

    $this->actingAs(entryAdmin())
        ->delete(route('admin.show-sections.show-classes.entries.destroy', [$section, $class, $entry]))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('entries', ['id' => $entry->id]);
});

it('exhibitor fee recalculates after adding an entry', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();

    expect($exhibitor->feeOwedPence())->toBe(0);

    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    expect($exhibitor->fresh()->feeOwedPence())->toBe(config('show.entry_fee_pence'));
});

it('exhibitor fee recalculates after deleting an entry', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    expect($exhibitor->fresh()->feeOwedPence())->toBe(config('show.entry_fee_pence'));

    $this->actingAs(entryAdmin())
        ->delete(route('admin.show-sections.show-classes.entries.destroy', [$section, $class, $entry]));

    expect($exhibitor->fresh()->feeOwedPence())->toBe(0);
});

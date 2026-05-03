<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('add entries page loads for an exhibitor', function () {
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.add-entry', $exhibitor))
        ->assertOk()
        ->assertSee('Alice Smith');
});

it('admin can add an entry by selecting section and class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.exhibitors.store-entry', $exhibitor), [
            'show_class_id' => $class->id,
            'quantity' => 1,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('entries', [
        'show_class_id' => $class->id,
        'exhibitor_id' => $exhibitor->id,
    ]);
});

it('admin can add multiple entries at once', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'max_entries_per_exhibitor' => 5]);
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.exhibitors.store-entry', $exhibitor), [
            'show_class_id' => $class->id,
            'quantity' => 3,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', '3 entries added.');

    expect(Entry::where('show_class_id', $class->id)->where('exhibitor_id', $exhibitor->id)->count())->toBe(3);
});

it('successful entry redirects to labels page', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.exhibitors.store-entry', $exhibitor), [
            'show_class_id' => $class->id,
            'quantity' => 1,
        ]);

    $response->assertRedirect();
    $this->assertStringContainsString(
        route('admin.exhibitors.labels', $exhibitor),
        $response->headers->get('Location')
    );
    $response->assertSessionHas('success');
});

it('entry appears in the exhibitor entry list after creation', function () {
    $section = ShowSection::factory()->create(['name' => 'Flowers']);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Best Rose']);
    $exhibitor = Exhibitor::factory()->create();

    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.add-entry', $exhibitor))
        ->assertOk()
        ->assertSee('Flowers')
        ->assertSee('Best Rose');
});

it('exhibitor cannot exceed max_entries_per_exhibitor via add entry page', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'max_entries_per_exhibitor' => 1]);
    $exhibitor = Exhibitor::factory()->create();

    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.exhibitors.store-entry', $exhibitor), [
            'show_class_id' => $class->id,
            'quantity' => 1,
        ])
        ->assertSessionHasErrors('show_class_id');
});

it('guest is redirected to login when accessing add entry page', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->get(route('admin.exhibitors.add-entry', $exhibitor))
        ->assertRedirect(route('login'));
});

it('labels page loads and shows entry numbers', function () {
    $section = ShowSection::factory()->create(['name' => 'Flowers']);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Best Rose']);
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.labels', $exhibitor))
        ->assertOk()
        ->assertSee('Alice Smith')
        ->assertSee((string) $entry->entry_number)
        ->assertSee('Best Rose');
});

it('labels page filters to specified entries when ids provided', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.exhibitors.labels', $exhibitor).'?entries[]='.$entry1->id)
        ->assertOk()
        ->assertSee('1 Label'); // only the filtered entry is shown
});

it('guest is redirected to login when accessing labels page', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->get(route('admin.exhibitors.labels', $exhibitor))
        ->assertRedirect(route('login'));
});

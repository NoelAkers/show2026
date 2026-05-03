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
        ])
        ->assertRedirect(route('admin.exhibitors.add-entry', $exhibitor));

    $this->assertDatabaseHas('entries', [
        'show_class_id' => $class->id,
        'exhibitor_id' => $exhibitor->id,
    ]);
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
        ])
        ->assertSessionHasErrors('show_class_id');
});

it('page stays on add-entry after each successful entry', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.exhibitors.store-entry', $exhibitor), [
            'show_class_id' => $class->id,
        ])
        ->assertRedirect(route('admin.exhibitors.add-entry', $exhibitor))
        ->assertSessionHas('success');
});

it('guest is redirected to login when accessing add entry page', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->get(route('admin.exhibitors.add-entry', $exhibitor))
        ->assertRedirect(route('login'));
});

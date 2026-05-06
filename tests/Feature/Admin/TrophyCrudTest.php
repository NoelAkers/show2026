<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function trophyAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can create a trophy with a name and class assignments', function () {
    $class = ShowClass::factory()->create();

    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Best in Show',
            'class_ids' => [$class->id],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Best in Show')->first();
    expect($trophy)->not->toBeNull();
    expect($trophy->showClasses()->count())->toBe(1);
});

it('admin can create a trophy with no class assignments', function () {
    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Reserve Champion',
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Reserve Champion')->first();
    expect($trophy)->not->toBeNull();
    expect($trophy->showClasses()->count())->toBe(0);
});

it('admin can update name, description, and class assignments', function () {
    $trophy = Trophy::factory()->create(['name' => 'Old Name']);
    $class1 = ShowClass::factory()->create();
    $class2 = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class1->id);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => 'New Name',
            'description' => 'A great trophy',
            'class_ids' => [$class2->id],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy->refresh();
    expect($trophy->name)->toBe('New Name');
    expect($trophy->description)->toBe('A great trophy');

    $classIds = $trophy->showClasses()->pluck('show_classes.id')->toArray();
    expect($classIds)->toContain($class2->id);
    expect($classIds)->not->toContain($class1->id);
});

it('a class can be assigned to multiple trophies simultaneously', function () {
    $class = ShowClass::factory()->create();
    $trophy1 = Trophy::factory()->create();
    $trophy2 = Trophy::factory()->create();

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy1), [
            'name' => $trophy1->name,
            'class_ids' => [$class->id],
        ])
        ->assertRedirect();

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy2), [
            'name' => $trophy2->name,
            'class_ids' => [$class->id],
        ])
        ->assertRedirect();

    expect($trophy1->showClasses()->count())->toBe(1);
    expect($trophy2->showClasses()->count())->toBe(1);
});

it('admin can delete a trophy', function () {
    $trophy = Trophy::factory()->create();

    $this->actingAs(trophyAdmin())
        ->delete(route('admin.trophies.destroy', $trophy))
        ->assertRedirect(route('admin.trophies.index'));

    $this->assertDatabaseMissing('trophies', ['id' => $trophy->id]);
});

it('guest is redirected from trophy routes', function () {
    $this->get(route('admin.trophies.index'))
        ->assertRedirect(route('login'));
});

it('judge receives 403 on trophy routes', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.trophies.index'))
        ->assertForbidden();
});

it('trophy index shows the current winner for each trophy', function () {
    $trophy = Trophy::factory()->create(['name' => 'Grand Champion']);
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $winner = Exhibitor::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $winner->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '1st']);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee($winner->full_name);
});

it('trophy index shows "No winner yet" when no results entered', function () {
    $trophy = Trophy::factory()->create(['name' => 'Empty Trophy']);
    $class = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class->id);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee('No winner yet');
});

it('trophy index lists all tied winners', function () {
    $trophy = Trophy::factory()->create(['name' => 'Tied Trophy']);
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $exhibitor1 = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Adams']);
    $exhibitor2 = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Brown']);

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor2->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '1st']);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee($exhibitor1->full_name)
        ->assertSee($exhibitor2->full_name);
});

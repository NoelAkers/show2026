<?php

use App\Models\Entry;
use App\Models\PrizeLevel;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function classAdmin(): User
{
    return User::factory()->admin()->create();
}

function classJudge(): User
{
    return User::factory()->judge()->create();
}

it('admin can view class list for a section', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']);

    $this->actingAs(classAdmin())
        ->get(route('admin.show-sections.show-classes.index', $section))
        ->assertOk()
        ->assertSee($class->id.'. Roses');
});

it('class show page shows the class ID preceding the name', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']);

    $this->actingAs(classAdmin())
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee($class->id.'. Roses');
});

it('admin can create a class with valid data', function () {
    $section = ShowSection::factory()->create();
    $prizeLevel = PrizeLevel::factory()->create();

    $this->actingAs(classAdmin())
        ->post(route('admin.show-sections.show-classes.store', $section), [
            'name' => 'Dahlias',
            'prize_level_id' => $prizeLevel->id,
            'max_entries_per_exhibitor' => 3,
            'sort_order' => 1,
        ])
        ->assertRedirect(route('admin.show-sections.show-classes.index', $section));

    $this->assertDatabaseHas('show_classes', [
        'show_section_id' => $section->id,
        'name' => 'Dahlias',
        'prize_level_id' => $prizeLevel->id,
    ]);
});

it('duplicate class name within the same section fails validation', function () {
    $section = ShowSection::factory()->create();
    $prizeLevel = PrizeLevel::factory()->create();
    ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']);

    $this->actingAs(classAdmin())
        ->post(route('admin.show-sections.show-classes.store', $section), [
            'name' => 'Roses',
            'prize_level_id' => $prizeLevel->id,
            'max_entries_per_exhibitor' => 1,
            'sort_order' => 0,
        ])
        ->assertSessionHasErrors('name');
});

it('same class name in two different sections is allowed', function () {
    $section1 = ShowSection::factory()->create();
    $section2 = ShowSection::factory()->create();
    $prizeLevel = PrizeLevel::factory()->create();
    ShowClass::factory()->create(['show_section_id' => $section1->id, 'name' => 'Roses']);

    $this->actingAs(classAdmin())
        ->post(route('admin.show-sections.show-classes.store', $section2), [
            'name' => 'Roses',
            'prize_level_id' => $prizeLevel->id,
            'max_entries_per_exhibitor' => 1,
            'sort_order' => 0,
        ])
        ->assertRedirect(route('admin.show-sections.show-classes.index', $section2));
});

it('admin can update a class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Old Name']);

    $this->actingAs(classAdmin())
        ->put(route('admin.show-sections.show-classes.update', [$section, $class]), [
            'name' => 'New Name',
            'prize_level_id' => $class->prize_level_id,
            'max_entries_per_exhibitor' => 2,
            'sort_order' => 0,
        ])
        ->assertRedirect(route('admin.show-sections.show-classes.index', $section));

    expect($class->fresh()->name)->toBe('New Name');
});

it('admin can delete a class with no entries', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);

    $this->actingAs(classAdmin())
        ->delete(route('admin.show-sections.show-classes.destroy', [$section, $class]))
        ->assertRedirect(route('admin.show-sections.show-classes.index', $section));

    $this->assertDatabaseMissing('show_classes', ['id' => $class->id]);
});

it('admin cannot delete a class with entries', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs(classAdmin())
        ->delete(route('admin.show-sections.show-classes.destroy', [$section, $class]))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('show_classes', ['id' => $class->id]);
});

it('guest is redirected to login on class index', function () {
    $section = ShowSection::factory()->create();

    $this->get(route('admin.show-sections.show-classes.index', $section))
        ->assertRedirect(route('login'));
});

it('judge receives 403 on class index', function () {
    $section = ShowSection::factory()->create();

    $this->actingAs(classJudge())
        ->get(route('admin.show-sections.show-classes.index', $section))
        ->assertForbidden();
});

<?php

use App\Models\PrizeLevel;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Database\Seeders\PrizeLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prize levels are seeded with correct values', function () {
    $this->seed(PrizeLevelSeeder::class);

    $standard = PrizeLevel::where('name', 'Standard')->firstOrFail();
    expect($standard->first_place_pence)->toBe(100)
        ->and($standard->second_place_pence)->toBe(50)
        ->and($standard->third_place_pence)->toBe(25);

    $top = PrizeLevel::where('name', 'Top')->firstOrFail();
    expect($top->first_place_pence)->toBe(500)
        ->and($top->second_place_pence)->toBe(250)
        ->and($top->third_place_pence)->toBe(100);
});

it('creating a show class persists the prize level', function () {
    $section = ShowSection::factory()->create();
    $prizeLevel = PrizeLevel::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.show-sections.show-classes.store', $section), [
            'name' => 'Best Rose',
            'prize_level_id' => $prizeLevel->id,
            'max_entries_per_exhibitor' => 5,
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $class = ShowClass::where('name', 'Best Rose')->firstOrFail();
    expect($class->prize_level_id)->toBe($prizeLevel->id);
    expect($class->prizeLevel->name)->toBe($prizeLevel->name);
});

it('editing a show class can change its prize level', function () {
    $section = ShowSection::factory()->create();
    $original = PrizeLevel::factory()->create(['name' => 'Standard', 'first_place_pence' => 100]);
    $upgraded = PrizeLevel::factory()->create(['name' => 'Top', 'first_place_pence' => 500]);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'prize_level_id' => $original->id]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.show-sections.show-classes.update', [$section, $class]), [
            'name' => $class->name,
            'prize_level_id' => $upgraded->id,
            'max_entries_per_exhibitor' => $class->max_entries_per_exhibitor,
            'sort_order' => $class->sort_order,
        ])
        ->assertRedirect();

    expect($class->fresh()->prize_level_id)->toBe($upgraded->id);
});

it('prize_level_id is required when creating a class', function () {
    $section = ShowSection::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.show-sections.show-classes.store', $section), [
            'name' => 'Roses',
            'max_entries_per_exhibitor' => 5,
            'sort_order' => 0,
        ])
        ->assertSessionHasErrors('prize_level_id');
});

it('prize_level_id must exist in prize_levels table', function () {
    $section = ShowSection::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.show-sections.show-classes.store', $section), [
            'name' => 'Roses',
            'prize_level_id' => 9999,
            'max_entries_per_exhibitor' => 5,
            'sort_order' => 0,
        ])
        ->assertSessionHasErrors('prize_level_id');
});

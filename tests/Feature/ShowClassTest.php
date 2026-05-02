<?php

use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory creates a valid ShowClass linked to a ShowSection', function () {
    $class = ShowClass::factory()->create();

    expect($class->exists)->toBeTrue();
    expect($class->show_section_id)->not->toBeNull();
    expect($class->showSection)->toBeInstanceOf(ShowSection::class);
});

it('show_section_id is required', function () {
    expect(fn () => ShowClass::factory()->create(['show_section_id' => null]))
        ->toThrow(QueryException::class);
});

it('name must be unique within a section', function () {
    $section = ShowSection::factory()->create();
    ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']);

    expect(fn () => ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']))
        ->toThrow(QueryException::class);
});

it('same class name in two different sections is allowed', function () {
    $section1 = ShowSection::factory()->create();
    $section2 = ShowSection::factory()->create();

    ShowClass::factory()->create(['show_section_id' => $section1->id, 'name' => 'Roses']);
    $class2 = ShowClass::factory()->create(['show_section_id' => $section2->id, 'name' => 'Roses']);

    expect($class2->exists)->toBeTrue();
});

it('max_entries_per_exhibitor defaults to 5', function () {
    $class = ShowClass::factory()->create();

    expect($class->max_entries_per_exhibitor)->toBe(5);
});

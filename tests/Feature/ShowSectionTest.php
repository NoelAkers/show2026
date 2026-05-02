<?php

use App\Models\ShowSection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory creates a valid ShowSection', function () {
    $section = ShowSection::factory()->create();

    expect($section->exists)->toBeTrue();
    expect($section->name)->toBeString()->not->toBeEmpty();
});

it('name is required', function () {
    expect(fn () => ShowSection::factory()->create(['name' => null]))
        ->toThrow(QueryException::class);
});

it('sort_order defaults to 0', function () {
    $section = ShowSection::factory()->create(['sort_order' => 0]);

    expect($section->fresh()->sort_order)->toBe(0);
});

it('ordered scope returns sections in sort_order ASC', function () {
    ShowSection::factory()->create(['sort_order' => 3]);
    ShowSection::factory()->create(['sort_order' => 1]);
    ShowSection::factory()->create(['sort_order' => 2]);

    $sections = ShowSection::ordered()->get();

    expect($sections->pluck('sort_order')->all())->toBe([1, 2, 3]);
});

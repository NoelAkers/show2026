<?php

use App\Models\Entry;
use App\Models\Result;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory creates a valid Result', function () {
    $result = Result::factory()->create(['placement' => '1st']);

    expect($result->exists)->toBeTrue();
    expect($result->entry_id)->not->toBeNull();
});

it('points returns 3 for 1st', function () {
    $result = Result::factory()->make(['placement' => '1st']);
    expect($result->points())->toBe(3);
});

it('points returns 2 for 2nd', function () {
    $result = Result::factory()->make(['placement' => '2nd']);
    expect($result->points())->toBe(2);
});

it('points returns 1 for 3rd', function () {
    $result = Result::factory()->make(['placement' => '3rd']);
    expect($result->points())->toBe(1);
});

it('points returns 0 for highly_commended', function () {
    $result = Result::factory()->make(['placement' => 'highly_commended']);
    expect($result->points())->toBe(0);
});

it('points returns 0 for null placement', function () {
    $result = Result::factory()->make(['placement' => null]);
    expect($result->points())->toBe(0);
});

it('one result per entry is enforced by unique constraint', function () {
    $entry = Entry::factory()->create();
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '1st']);

    expect(fn () => Result::factory()->create(['entry_id' => $entry->id, 'placement' => '2nd']))
        ->toThrow(QueryException::class);
});

it('hasResult returns false when no result exists and true when it does', function () {
    $entry = Entry::factory()->create();

    expect($entry->hasResult())->toBeFalse();

    Result::factory()->create(['entry_id' => $entry->id]);

    expect($entry->hasResult())->toBeTrue();
});

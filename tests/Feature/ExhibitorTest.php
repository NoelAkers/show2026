<?php

use App\Models\Exhibitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory creates a valid adult exhibitor', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();

    expect($exhibitor->exists)->toBeTrue();
    expect($exhibitor->type)->toBe('adult');
    expect($exhibitor->isAdult())->toBeTrue();
    expect($exhibitor->isJunior())->toBeFalse();
});

it('factory creates a valid junior exhibitor', function () {
    $exhibitor = Exhibitor::factory()->junior()->create();

    expect($exhibitor->type)->toBe('junior');
    expect($exhibitor->isJunior())->toBeTrue();
    expect($exhibitor->isAdult())->toBeFalse();
});

it('resident and nonResident states set is_resident correctly', function () {
    $resident = Exhibitor::factory()->resident()->make();
    $nonResident = Exhibitor::factory()->nonResident()->make();

    expect($resident->is_resident)->toBeTrue();
    expect($nonResident->is_resident)->toBeFalse();
});

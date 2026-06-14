<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin factory state creates user with admin role', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin);
});

it('judge factory state creates user with judge role', function () {
    $user = User::factory()->judge()->create();

    expect($user->role)->toBe(UserRole::Judge);
});

it('helper factory state creates user with helper role', function () {
    $user = User::factory()->helper()->create();

    expect($user->role)->toBe(UserRole::Helper);
});

it('exhibitor factory state creates user with exhibitor role', function () {
    $user = User::factory()->exhibitor()->create();

    expect($user->role)->toBe(UserRole::Exhibitor);
});

it('steward factory state creates user with steward role', function () {
    $user = User::factory()->steward()->create();

    expect($user->role)->toBe(UserRole::Steward);
});

it('default factory creates user with exhibitor role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Exhibitor);
});

it('isAdmin returns true only for admin role', function () {
    $admin = User::factory()->admin()->make();
    $judge = User::factory()->judge()->make();

    expect($admin->isAdmin())->toBeTrue();
    expect($judge->isAdmin())->toBeFalse();
});

it('isJudge returns true only for judge role', function () {
    $judge = User::factory()->judge()->make();
    $admin = User::factory()->admin()->make();

    expect($judge->isJudge())->toBeTrue();
    expect($admin->isJudge())->toBeFalse();
});

it('isHelper returns true only for helper role', function () {
    $helper = User::factory()->helper()->make();
    $admin = User::factory()->admin()->make();

    expect($helper->isHelper())->toBeTrue();
    expect($admin->isHelper())->toBeFalse();
});

it('isExhibitor returns true only for exhibitor role', function () {
    $exhibitor = User::factory()->exhibitor()->make();
    $admin = User::factory()->admin()->make();

    expect($exhibitor->isExhibitor())->toBeTrue();
    expect($admin->isExhibitor())->toBeFalse();
});

it('isSteward returns true only for steward role', function () {
    $steward = User::factory()->steward()->make();
    $admin = User::factory()->admin()->make();

    expect($steward->isSteward())->toBeTrue();
    expect($admin->isSteward())->toBeFalse();
});

it('role is cast to UserRole enum', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBeInstanceOf(UserRole::class);
});

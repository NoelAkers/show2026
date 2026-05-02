<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('admin factory state creates user with role admin', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe('admin');
});

it('judge factory state creates user with role judge', function () {
    $user = User::factory()->judge()->create();

    expect($user->role)->toBe('judge');
});

it('isAdmin returns true only for admin role', function () {
    $admin = User::factory()->admin()->make();
    $judge = User::factory()->judge()->make();

    expect($admin->isAdmin())->toBeTrue();
    expect($judge->isAdmin())->toBeFalse();
});

it('isJudge returns true only for judge role', function () {
    $admin = User::factory()->admin()->make();
    $judge = User::factory()->judge()->make();

    expect($judge->isJudge())->toBeTrue();
    expect($admin->isJudge())->toBeFalse();
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin factory state creates user with role admin', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe('admin');
});

it('judge factory state creates user with role judge and is_judge true', function () {
    $user = User::factory()->judge()->create();

    expect($user->role)->toBe('judge')
        ->and($user->is_judge)->toBeTrue();
});

it('isAdmin returns true only for admin role', function () {
    $admin = User::factory()->admin()->make();
    $judge = User::factory()->judge()->make();

    expect($admin->isAdmin())->toBeTrue();
    expect($judge->isAdmin())->toBeFalse();
});

it('isJudge returns true when is_judge is true', function () {
    $judge = User::factory()->judge()->make();
    $admin = User::factory()->admin()->make();

    expect($judge->isJudge())->toBeTrue();
    expect($admin->isJudge())->toBeFalse();
});

it('isJudge returns true for admin who is also a judge', function () {
    $user = User::factory()->admin()->make(['is_judge' => true]);

    expect($user->isAdmin())->toBeTrue()
        ->and($user->isJudge())->toBeTrue();
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('guest is redirected to login on admin route', function () {
    $this->get(route('admin.show-sections.index'))
        ->assertRedirect(route('login'));
});

it('judge receives 403 on an admin-only route', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs($judge)
        ->get(route('admin.show-sections.index'))
        ->assertForbidden();
});

it('admin can access admin routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.show-sections.index'))
        ->assertOk();
});

it('judge can access judge routes', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs($judge)
        ->get(route('judge.sections.index'))
        ->assertOk();
});

it('admin can also access judge routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('judge.sections.index'))
        ->assertOk();
});

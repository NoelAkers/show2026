<?php

use App\Models\ShowClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('guest is redirected to login', function () {
    $this->get(route('admin.class-cards'))
        ->assertRedirect(route('login'));
});

it('admin can access class cards', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.class-cards'))
        ->assertOk();
});

it('class cards view shows class id and name', function () {
    $admin = User::factory()->admin()->create();
    $class = ShowClass::factory()->create(['name' => 'Best Bantam', 'description' => 'Open to any bantam breed']);

    $this->actingAs($admin)
        ->get(route('admin.class-cards'))
        ->assertOk()
        ->assertSee((string) $class->id)
        ->assertSee('Best Bantam')
        ->assertSee('Open to any bantam breed');
});

it('class cards view shows empty message when no classes exist', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.class-cards'))
        ->assertOk()
        ->assertSee('No classes to print.');
});

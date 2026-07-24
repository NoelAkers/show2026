<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stewardAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can add a steward with a valid email address', function () {
    $this->actingAs(stewardAdmin())
        ->post(route('admin.stewards.store'), [
            'name' => 'New Steward',
            'email' => 'newsteward@example.com',
        ])
        ->assertRedirect(route('admin.stewards.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'New Steward',
        'email' => 'newsteward@example.com',
        'role' => 'steward',
    ]);
});

it('email without a TLD is rejected when creating a steward', function () {
    $this->actingAs(stewardAdmin())
        ->post(route('admin.stewards.store'), [
            'name' => 'New Steward',
            'email' => 'newsteward@doe',
        ])
        ->assertSessionHasErrors(['email']);

    $this->assertDatabaseMissing('users', ['name' => 'New Steward']);
});

it('email without a TLD is rejected when updating a steward, without overwriting the existing one', function () {
    $steward = User::factory()->steward()->create(['email' => 'original@example.com']);

    $this->actingAs(stewardAdmin())
        ->put(route('admin.stewards.update', $steward), [
            'name' => $steward->name,
            'email' => 'jane@doe',
        ])
        ->assertSessionHasErrors(['email']);

    expect($steward->fresh()->email)->toBe('original@example.com');
});

<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function userAdmin(): User
{
    return User::factory()->admin()->create();
}

// --- Access control ---

it('guest is redirected from user routes', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

it('non-admin receives 403 on user routes', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
})->with(['judge', 'helper', 'exhibitor', 'steward']);

// --- Index ---

it('admin can view the user list', function () {
    User::factory()->exhibitor()->create(['name' => 'Jane Exhibitor']);

    $this->actingAs(userAdmin())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Jane Exhibitor');
});

it('user list shows all roles', function () {
    User::factory()->judge()->create(['name' => 'A Judge']);
    User::factory()->helper()->create(['name' => 'A Helper']);

    $this->actingAs(userAdmin())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('A Judge')
        ->assertSee('A Helper');
});

// --- Create ---

it('admin can view the create user form', function () {
    $this->actingAs(userAdmin())
        ->get(route('admin.users.create'))
        ->assertOk();
});

it('admin can create a user with any role', function () {
    $this->actingAs(userAdmin())
        ->post(route('admin.users.store'), [
            'name' => 'New Helper',
            'email' => 'helper@example.com',
            'role' => 'helper',
        ])
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'helper@example.com',
        'role' => 'helper',
    ]);
});

it('duplicate email is rejected on create', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs(userAdmin())
        ->post(route('admin.users.store'), [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'role' => 'exhibitor',
        ])
        ->assertSessionHasErrors(['email']);
});

it('role is required on create', function () {
    $this->actingAs(userAdmin())
        ->post(route('admin.users.store'), [
            'name' => 'No Role',
            'email' => 'norole@example.com',
        ])
        ->assertSessionHasErrors(['role']);
});

it('creating a user sends a password reset notification', function () {
    Notification::fake();

    $this->actingAs(userAdmin())
        ->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'role' => 'exhibitor',
        ]);

    $user = User::where('email', 'new@example.com')->first();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('creating a user stores a password reset token', function () {
    $this->actingAs(userAdmin())
        ->post(route('admin.users.store'), [
            'name' => 'Token User',
            'email' => 'token@example.com',
            'role' => 'exhibitor',
        ]);

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'token@example.com',
    ]);
});

// --- Edit / Update ---

it('admin can view the edit user form', function () {
    $user = User::factory()->exhibitor()->create(['name' => 'Edit Me']);

    $this->actingAs(userAdmin())
        ->get(route('admin.users.edit', $user))
        ->assertOk()
        ->assertSee('Edit Me');
});

it('admin can promote a self-registered exhibitor to helper', function () {
    $exhibitor = User::factory()->exhibitor()->create();

    $this->actingAs(userAdmin())
        ->put(route('admin.users.update', $exhibitor), [
            'name' => $exhibitor->name,
            'email' => $exhibitor->email,
            'role' => 'helper',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($exhibitor->fresh()->role)->toBe(UserRole::Helper);
});

it('admin cannot change their own role', function () {
    $admin = userAdmin();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'exhibitor',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($admin->fresh()->role)->toBe(UserRole::Admin);
});

it('email must be unique on update', function () {
    $other = User::factory()->create(['email' => 'other@example.com']);
    $user = User::factory()->exhibitor()->create();

    $this->actingAs(userAdmin())
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => 'other@example.com',
            'role' => 'exhibitor',
        ])
        ->assertSessionHasErrors(['email']);
});

// --- Destroy ---

it('admin can delete another user', function () {
    $user = User::factory()->exhibitor()->create();

    $this->actingAs(userAdmin())
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('admin cannot delete their own account', function () {
    $admin = userAdmin();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

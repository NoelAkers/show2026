<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    config(['show.self_entry_open' => false]);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('exhibitor.closed', absolute: false));

    $this->assertAuthenticated();
});

test('duplicate email registration shows helpful family member message', function () {
    User::factory()->create(['email' => 'shared@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Another Person',
        'email' => 'shared@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(session('errors')->get('email')[0])
        ->toContain('Each exhibitor must register with their own individual email address');
});

test('newly registered user has exhibitor role', function () {
    $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'jane@example.com')->sole();

    expect($user->role)->toBe(UserRole::Exhibitor);
});

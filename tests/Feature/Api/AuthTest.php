<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('judge can login and receives token', function () {
    $user = User::factory()->judge()->create(['password' => bcrypt('secret')]);

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret'])
        ->assertOk()
        ->assertJsonStructure(['token', 'user' => ['name']]);
});

test('non-judge cannot login', function () {
    $user = User::factory()->exhibitor()->create(['password' => bcrypt('secret')]);

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret'])
        ->assertUnauthorized();
});

test('wrong password returns 401', function () {
    $user = User::factory()->judge()->create(['password' => bcrypt('secret')]);

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnauthorized();
});

test('login validates required fields', function () {
    $this->postJson('/api/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login rejects an email address without a TLD', function () {
    $this->postJson('/api/login', ['email' => 'judge@doe', 'password' => 'secret'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('protected endpoints require a token', function () {
    $this->getJson('/api/show-classes?number=1')->assertUnauthorized();
    $this->getJson('/api/entries/1')->assertUnauthorized();
    $this->postJson('/api/results')->assertUnauthorized();
    $this->postJson('/api/logout')->assertUnauthorized();
});

test('judge can logout and token is deleted', function () {
    $user = User::factory()->judge()->create();
    $token = $user->createToken('test')->plainTextToken;

    expect($user->tokens()->count())->toBe(1);

    $this->withToken($token)->postJson('/api/logout')->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});

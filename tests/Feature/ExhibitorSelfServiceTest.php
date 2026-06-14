<?php

use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['show.self_entry_open' => true]);
});

// --- Access control ---

it('guest cannot access exhibitor dashboard', function () {
    $this->get(route('exhibitor.dashboard'))
        ->assertRedirect(route('login'));
});

it('non-exhibitor role receives 403 on exhibitor routes', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get(route('exhibitor.dashboard'))
        ->assertForbidden();
})->with(['admin', 'judge', 'helper', 'steward']);

it('exhibitor is redirected to closed page when self-entry is closed', function () {
    config(['show.self_entry_open' => false]);

    $user = User::factory()->exhibitor()->create();

    $this->actingAs($user)
        ->get(route('exhibitor.dashboard'))
        ->assertRedirect(route('exhibitor.closed'));
});

// --- First login auto-creation ---

it('auto-creates an exhibitor record on first dashboard visit and redirects to profile edit', function () {
    $user = User::factory()->exhibitor()->create(['name' => 'Jane Smith']);

    expect($user->exhibitor)->toBeNull();

    $this->actingAs($user)
        ->get(route('exhibitor.dashboard'))
        ->assertRedirect(route('exhibitor.profile.edit'));

    $exhibitor = $user->fresh()->exhibitor;
    expect($exhibitor)->not->toBeNull();
    expect($exhibitor->full_name)->toBe('Jane Smith');
    expect($exhibitor->user_id)->toBe($user->id);
});

it('does not create a duplicate exhibitor record on subsequent dashboard visits', function () {
    $user = User::factory()->withExhibitor()->create();

    $this->actingAs($user)->get(route('exhibitor.dashboard'));
    $this->actingAs($user)->get(route('exhibitor.dashboard'));

    expect(Exhibitor::where('user_id', $user->id)->count())->toBe(1);
});

it('shows the dashboard when an exhibitor record already exists', function () {
    $user = User::factory()->withExhibitor()->create();

    $this->actingAs($user)
        ->get(route('exhibitor.dashboard'))
        ->assertOk()
        ->assertViewIs('exhibitor.dashboard');
});

// --- Profile edit ---

it('exhibitor can view the profile edit page', function () {
    $user = User::factory()->withExhibitor()->create();

    $this->actingAs($user)
        ->get(route('exhibitor.profile.edit'))
        ->assertOk()
        ->assertViewIs('exhibitor.edit');
});

it('exhibitor can update their editable profile fields', function () {
    $user = User::factory()->withExhibitor()->create();

    $this->actingAs($user)->put(route('exhibitor.profile.update'), [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'full_name' => 'Jane Smith',
        'sort_name' => 'Smith, Jane',
        'type' => 'junior',
        'is_resident' => true,
        'is_novice' => false,
    ])->assertRedirect(route('exhibitor.dashboard'));

    $exhibitor = $user->fresh()->exhibitor;
    expect($exhibitor->first_name)->toBe('Jane');
    expect($exhibitor->last_name)->toBe('Smith');
    expect($exhibitor->type)->toBe('junior');
    expect($exhibitor->is_resident)->toBeTrue();
    expect($exhibitor->is_novice)->toBeFalse();
});

it('exhibitor cannot update has_paid via the profile update endpoint', function () {
    $user = User::factory()->withExhibitor()->create();
    $exhibitor = $user->exhibitor;
    $exhibitor->update(['has_paid' => false]);

    $this->actingAs($user)->put(route('exhibitor.profile.update'), [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'full_name' => 'Jane Smith',
        'sort_name' => 'Smith, Jane',
        'type' => 'adult',
        'has_paid' => true,
        'amount_paid_pence' => 9999,
    ]);

    $exhibitor->refresh();
    expect($exhibitor->has_paid)->toBeFalse();
    expect($exhibitor->amount_paid_pence)->toBe(0);
});

it('profile update requires all required fields', function () {
    $user = User::factory()->withExhibitor()->create();

    $this->actingAs($user)
        ->put(route('exhibitor.profile.update'), [])
        ->assertSessionHasErrors(['first_name', 'last_name', 'full_name', 'sort_name', 'type']);
});

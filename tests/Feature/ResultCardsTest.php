<?php

use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

// --- Result model helper methods ---

it('isPrinted returns false when card_printed_at is null', function () {
    $result = Result::factory()->create(['placement' => '1st', 'card_printed_at' => null]);

    expect($result->isPrinted())->toBeFalse();
});

it('isPrinted returns true when card_printed_at is set', function () {
    $result = Result::factory()->create(['placement' => '1st', 'card_printed_at' => now()]);

    expect($result->isPrinted())->toBeTrue();
});

it('needsReprint returns false when not yet printed', function () {
    $result = Result::factory()->create(['placement' => '1st', 'card_printed_at' => null]);

    expect($result->needsReprint())->toBeFalse();
});

it('needsReprint returns false when printed after last update', function () {
    $result = Result::factory()->create(['placement' => '1st']);
    $result->update(['card_printed_at' => now()]);

    expect($result->fresh()->needsReprint())->toBeFalse();
});

it('needsReprint returns true when updated after printing', function () {
    $result = Result::factory()->create([
        'placement' => '1st',
        'card_printed_at' => Carbon::now()->subMinute(),
    ]);
    $result->update(['placement' => '2nd']);

    expect($result->fresh()->needsReprint())->toBeTrue();
});

it('prizeColour returns configured colour for placement', function () {
    $result = Result::factory()->create(['placement' => '1st']);

    expect($result->prizeColour())->toBe(config('show.result_card_colours.1st'));
});

it('prizeColour falls back to dark colour for unknown placement', function () {
    $result = Result::factory()->create(['placement' => null]);

    expect($result->prizeColour())->toBe('#111827');
});

// --- Controller ---

it('guest is redirected to login', function () {
    $this->get(route('admin.result-cards'))
        ->assertRedirect(route('login'));
});

it('admin can access result cards', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.result-cards'))
        ->assertOk();
});

it('shows empty message when no unprinted results exist', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.result-cards'))
        ->assertOk()
        ->assertSee('All result cards have been printed.');
});

it('default filter shows only unprinted and needs-reprint results', function () {
    $admin = User::factory()->admin()->create();

    $printed = Result::factory()->create(['placement' => '1st', 'card_printed_at' => now()]);
    $unprinted = Result::factory()->create(['placement' => '2nd', 'card_printed_at' => null]);
    $needsReprint = Result::factory()->create([
        'placement' => '3rd',
        'card_printed_at' => Carbon::now()->subMinute(),
    ]);
    $needsReprint->update(['placement' => '2nd']);

    $response = $this->actingAs($admin)
        ->get(route('admin.result-cards'))
        ->assertOk();

    $response->assertSee($unprinted->entry->exhibitor->full_name);
    $response->assertSee($needsReprint->fresh()->entry->exhibitor->full_name);
    $response->assertDontSee($printed->entry->exhibitor->full_name);
});

it('all filter shows every result with a placement', function () {
    $admin = User::factory()->admin()->create();

    $printed = Result::factory()->create(['placement' => '1st', 'card_printed_at' => now()]);
    $unprinted = Result::factory()->create(['placement' => '2nd', 'card_printed_at' => null]);

    $this->actingAs($admin)
        ->get(route('admin.result-cards', ['filter' => 'all']))
        ->assertOk()
        ->assertSee($printed->entry->exhibitor->full_name)
        ->assertSee($unprinted->entry->exhibitor->full_name);
});

it('card shows prize label, winner name, show title and entry info', function () {
    $admin = User::factory()->admin()->create();
    $result = Result::factory()->create(['placement' => '1st', 'card_printed_at' => null]);
    $entry = $result->entry;

    $this->actingAs($admin)
        ->get(route('admin.result-cards'))
        ->assertOk()
        ->assertSee('1st Prize')
        ->assertSee($entry->exhibitor->full_name)
        ->assertSee(config('show.title'))
        ->assertSee((string) $entry->entry_number)
        ->assertSee((string) $entry->exhibitor->id);
});

it('mark printed sets card_printed_at on the given results', function () {
    $admin = User::factory()->admin()->create();
    $result = Result::factory()->create(['placement' => '1st', 'card_printed_at' => null]);

    $this->actingAs($admin)
        ->post(route('admin.result-cards.mark-printed'), ['result_ids' => [$result->id]])
        ->assertRedirect(route('admin.result-cards'));

    expect($result->fresh()->card_printed_at)->not->toBeNull();
});

it('mark printed requires auth', function () {
    $result = Result::factory()->create(['placement' => '1st']);

    $this->post(route('admin.result-cards.mark-printed'), ['result_ids' => [$result->id]])
        ->assertRedirect(route('login'));
});

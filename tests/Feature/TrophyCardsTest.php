<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

// --- Trophy model helper methods ---

it('isPrinted returns false when card_printed_at is null', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create(['card_printed_at' => null]);

    expect($trophy->isPrinted())->toBeFalse();
});

it('isPrinted returns true when card_printed_at is set', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create(['card_printed_at' => now()]);

    expect($trophy->isPrinted())->toBeTrue();
});

it('needsReprint returns false when not yet printed', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create(['card_printed_at' => null]);

    expect($trophy->needsReprint())->toBeFalse();
});

it('needsReprint returns false when printed after last update', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create();
    $trophy->update(['card_printed_at' => now()]);

    expect($trophy->fresh()->needsReprint())->toBeFalse();
});

it('needsReprint returns true when updated after printing', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create([
        'card_printed_at' => Carbon::now()->subMinute(),
    ]);
    $trophy->update(['name' => 'Updated Name']);

    expect($trophy->fresh()->needsReprint())->toBeTrue();
});

// --- Controller ---

it('guest is redirected to login', function () {
    $this->get(route('admin.trophy-cards'))
        ->assertRedirect(route('login'));
});

it('admin can access trophy cards', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.trophy-cards'))
        ->assertOk();
});

it('shows empty message when no unprinted trophy winners exist', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.trophy-cards'))
        ->assertOk()
        ->assertSee('All trophy cards have been printed.');
});

it('default filter shows only unprinted and needs-reprint trophies', function () {
    $admin = User::factory()->admin()->create();

    $printed = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
        'card_printed_at' => now(),
    ]);
    $unprinted = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
        'card_printed_at' => null,
    ]);
    $needsReprint = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
        'card_printed_at' => Carbon::now()->subMinute(),
    ]);
    $needsReprint->update(['name' => 'Reprint Trophy']);

    $response = $this->actingAs($admin)
        ->get(route('admin.trophy-cards'))
        ->assertOk();

    $response->assertSee($unprinted->name);
    $response->assertSee($needsReprint->fresh()->name);
    $response->assertDontSee($printed->name);
});

it('all filter shows every judge-awarded trophy with a winner', function () {
    $admin = User::factory()->admin()->create();

    $printed = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
        'card_printed_at' => now(),
    ]);
    $unprinted = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
        'card_printed_at' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.trophy-cards', ['filter' => 'all']))
        ->assertOk()
        ->assertSee($printed->name)
        ->assertSee($unprinted->name);
});

it('excludes points-based trophies', function () {
    $admin = User::factory()->admin()->create();

    $pointsBased = Trophy::factory()->pointsBased()->create(['name' => 'Points Trophy']);

    $this->actingAs($admin)
        ->get(route('admin.trophy-cards', ['filter' => 'all']))
        ->assertOk()
        ->assertDontSee('Points Trophy');
});

it('excludes judge-awarded trophies without a recorded winner', function () {
    $admin = User::factory()->admin()->create();

    $noWinner = Trophy::factory()->judgeAwarded()->create(['name' => 'No Winner Trophy', 'winning_entry_id' => null]);

    $this->actingAs($admin)
        ->get(route('admin.trophy-cards', ['filter' => 'all']))
        ->assertOk()
        ->assertDontSee('No Winner Trophy');
});

it('card shows trophy name, winner name, show title and entry number', function () {
    $admin = User::factory()->admin()->create();
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Winning Exhibitor']);
    $entry = Entry::factory()->for($exhibitor)->create();
    $trophy = Trophy::factory()->judgeAwarded()->create([
        'name' => 'Best in Show',
        'winning_entry_id' => $entry->id,
        'card_printed_at' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.trophy-cards'))
        ->assertOk()
        ->assertSee($trophy->name)
        ->assertSee('Winning Exhibitor')
        ->assertSee(config('show.title'))
        ->assertSee($entry->formatted_entry_number)
        ->assertSee(asset('images/trophy.png'), false);
});

it('mark printed sets card_printed_at on the given trophies', function () {
    $admin = User::factory()->admin()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
        'card_printed_at' => null,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.trophy-cards.mark-printed'), ['trophy_ids' => [$trophy->id]])
        ->assertRedirect(route('admin.trophy-cards'));

    expect($trophy->fresh()->card_printed_at)->not->toBeNull();
});

it('mark printed requires auth', function () {
    $trophy = Trophy::factory()->judgeAwarded()->create([
        'winning_entry_id' => Entry::factory()->create()->id,
    ]);

    $this->post(route('admin.trophy-cards.mark-printed'), ['trophy_ids' => [$trophy->id]])
        ->assertRedirect(route('login'));
});

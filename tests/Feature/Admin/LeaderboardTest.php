<?php

use App\Livewire\Admin\Leaderboard;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists trophies with name, description and type', function () {
    $admin = User::factory()->admin()->create();
    Trophy::factory()->pointsBased()->create(['name' => 'Best in Show', 'description' => 'The top prize']);

    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee('Best in Show')
        ->assertSee('The top prize')
        ->assertSee('Points');
});

it('does not show trophy management controls', function () {
    $admin = User::factory()->admin()->create();
    Trophy::factory()->pointsBased()->create();

    $response = $this->actingAs($admin)->get(route('admin.leaderboard'));

    $response->assertOk()
        ->assertDontSee('Add Trophy')
        ->assertDontSee('Delete');
});

it('shows the trophy cards and trophy list buttons', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee('Trophy Cards')
        ->assertSee('Trophy List')
        ->assertSee(route('admin.trophy-cards'), false)
        ->assertSee(route('admin.trophy-list'), false);
});

it('links a points-based trophy leader to its trophy leaderboard', function () {
    $admin = User::factory()->admin()->create();
    $trophy = Trophy::factory()->pointsBased()->create();

    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee(route('admin.trophies.leaderboard', $trophy), false);
});

it('guest is redirected to login', function () {
    $this->get(route('admin.leaderboard'))
        ->assertRedirect(route('login'));
});

it('judge receives 403', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.leaderboard'))
        ->assertForbidden();
});

it('admin can save a winning entry for a judge-awarded trophy by entry number', function () {
    $admin = User::factory()->admin()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Winning Exhibitor']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $component = Livewire::actingAs($admin)
        ->test(Leaderboard::class)
        ->set("winningEntries.{$trophy->id}", $entry->formatted_entry_number)
        ->call('saveTrophy', $trophy->id);

    expect($trophy->fresh()->winning_entry_id)->toBe($entry->id);
    $component->assertSee('Winning Exhibitor');
});

it('shows the current leader name and winning entry number for a judge-awarded trophy', function () {
    $admin = User::factory()->admin()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Winning Exhibitor']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Trophy::factory()->judgeAwarded()->create(['winning_entry_id' => $entry->id]);

    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee('Winning Exhibitor')
        ->assertSee($entry->formatted_entry_number);
});

it('shows "No winner yet" for a judge-awarded trophy with no winning entry', function () {
    $admin = User::factory()->admin()->create();
    Trophy::factory()->judgeAwarded()->create(['winning_entry_id' => null]);

    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee('No winner yet');
});

it('admin sees an error when entering an invalid entry number', function () {
    $admin = User::factory()->admin()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create();

    Livewire::actingAs($admin)
        ->test(Leaderboard::class)
        ->set("winningEntries.{$trophy->id}", '999')
        ->call('saveTrophy', $trophy->id)
        ->assertHasErrors(["winningEntries.{$trophy->id}"]);

    expect($trophy->fresh()->winning_entry_id)->toBeNull();
});

it('admin can clear the winning entry by submitting an empty entry number', function () {
    $admin = User::factory()->admin()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $trophy = Trophy::factory()->judgeAwarded()->create(['winning_entry_id' => $entry->id]);

    Livewire::actingAs($admin)
        ->test(Leaderboard::class)
        ->set("winningEntries.{$trophy->id}", '')
        ->call('saveTrophy', $trophy->id);

    expect($trophy->fresh()->winning_entry_id)->toBeNull();
});

<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('guest is redirected to login', function () {
    $this->get(route('admin.trophy-list'))
        ->assertRedirect(route('login'));
});

it('judge receives 403', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.trophy-list'))
        ->assertForbidden();
});

it('admin sees trophy names and current leaders', function () {
    $admin = User::factory()->admin()->create();
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Winning Exhibitor']);
    $entry = Entry::factory()->for($exhibitor)->create();
    Trophy::factory()->judgeAwarded()->create([
        'name' => 'Best in Show',
        'winning_entry_id' => $entry->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.trophy-list'))
        ->assertOk()
        ->assertSee('Best in Show')
        ->assertSee('Winning Exhibitor');
});

it('shows a placeholder for trophies with no winner yet', function () {
    $admin = User::factory()->admin()->create();
    Trophy::factory()->judgeAwarded()->create(['name' => 'Empty Trophy', 'winning_entry_id' => null]);

    $this->actingAs($admin)
        ->get(route('admin.trophy-list'))
        ->assertOk()
        ->assertSee('Not yet awarded');
});

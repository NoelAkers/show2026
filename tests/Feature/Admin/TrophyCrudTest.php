<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function trophyAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can create a trophy with a name and class assignments', function () {
    $class = ShowClass::factory()->create();

    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Best in Show',
            'class_ids' => [$class->id],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Best in Show')->first();
    expect($trophy)->not->toBeNull();
    expect($trophy->showClasses()->count())->toBe(1);
});

it('admin can create a trophy with no class assignments', function () {
    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Reserve Champion',
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Reserve Champion')->first();
    expect($trophy)->not->toBeNull();
    expect($trophy->showClasses()->count())->toBe(0);
});

it('admin can update name, description, and class assignments', function () {
    $trophy = Trophy::factory()->create(['name' => 'Old Name']);
    $class1 = ShowClass::factory()->create();
    $class2 = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class1->id);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => 'New Name',
            'description' => 'A great trophy',
            'class_ids' => [$class2->id],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy->refresh();
    expect($trophy->name)->toBe('New Name');
    expect($trophy->description)->toBe('A great trophy');

    $classIds = $trophy->showClasses()->pluck('show_classes.id')->toArray();
    expect($classIds)->toContain($class2->id);
    expect($classIds)->not->toContain($class1->id);
});

it('create form shows each class preceded by its class ID', function () {
    $class = ShowClass::factory()->create(['name' => 'Calverley Master Gardener']);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.create'))
        ->assertSee($class->id.'. '.$class->name);
});

it('edit form shows each class preceded by its class ID', function () {
    $trophy = Trophy::factory()->create();
    $class = ShowClass::factory()->create(['name' => 'Best Rose']);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.edit', $trophy))
        ->assertSee($class->id.'. '.$class->name);
});

it('a class can be assigned to multiple trophies simultaneously', function () {
    $class = ShowClass::factory()->create();
    $trophy1 = Trophy::factory()->create();
    $trophy2 = Trophy::factory()->create();

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy1), [
            'name' => $trophy1->name,
            'class_ids' => [$class->id],
        ])
        ->assertRedirect();

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy2), [
            'name' => $trophy2->name,
            'class_ids' => [$class->id],
        ])
        ->assertRedirect();

    expect($trophy1->showClasses()->count())->toBe(1);
    expect($trophy2->showClasses()->count())->toBe(1);
});

it('admin can delete a trophy', function () {
    $trophy = Trophy::factory()->create();

    $this->actingAs(trophyAdmin())
        ->delete(route('admin.trophies.destroy', $trophy))
        ->assertRedirect(route('admin.trophies.index'));

    $this->assertDatabaseMissing('trophies', ['id' => $trophy->id]);
});

it('guest is redirected from trophy routes', function () {
    $this->get(route('admin.trophies.index'))
        ->assertRedirect(route('login'));
});

it('judge receives 403 on trophy routes', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.trophies.index'))
        ->assertForbidden();
});

it('trophy index shows the current winner for each trophy', function () {
    $trophy = Trophy::factory()->create(['name' => 'Grand Champion']);
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $winner = Exhibitor::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $winner->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '1st']);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee($winner->full_name);
});

it('trophy index shows "No winner yet" when no results entered', function () {
    $trophy = Trophy::factory()->create(['name' => 'Empty Trophy']);
    $class = ShowClass::factory()->create();
    $trophy->showClasses()->attach($class->id);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee('No winner yet');
});

it('trophy index shows type badge for each trophy', function () {
    Trophy::factory()->pointsBased()->create(['name' => 'Points Trophy']);
    $judge = User::factory()->judge()->create();
    Trophy::factory()->judgeAwarded()->create(['name' => 'Judge Trophy', 'judge_id' => $judge->id]);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee('Points')
        ->assertSee('Judge');
});

it('admin can create a judge-awarded trophy with a steward', function () {
    $judge = User::factory()->judge()->create(['name' => 'Dr. Smith']);
    $steward = User::factory()->steward()->create(['name' => 'Jane Steward']);

    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Special Award',
            'is_points_based' => '0',
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Special Award')->first();
    expect($trophy->steward_id)->toBe($steward->id);
});

it('admin can create a judge-awarded trophy', function () {
    $judge = User::factory()->judge()->create(['name' => 'Dr. Smith']);

    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Special Award',
            'is_points_based' => '0',
            'judge_id' => $judge->id,
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Special Award')->first();
    expect($trophy)->not->toBeNull();
    expect($trophy->is_points_based)->toBeFalse();
    expect($trophy->judge_id)->toBe($judge->id);
    expect($trophy->showClasses()->count())->toBe(0);
});

it('judge_id is required when trophy is not points-based', function () {
    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Missing Judge',
            'is_points_based' => '0',
        ])
        ->assertSessionHasErrors('judge_id');
});

it('admin can update a trophy from points-based to judge-awarded', function () {
    $judge = User::factory()->judge()->create();
    $class = ShowClass::factory()->create();
    $trophy = Trophy::factory()->pointsBased()->create();
    $trophy->showClasses()->attach($class->id);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => $trophy->name,
            'is_points_based' => '0',
            'judge_id' => $judge->id,
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy->refresh();
    expect($trophy->is_points_based)->toBeFalse();
    expect($trophy->judge_id)->toBe($judge->id);
    expect($trophy->showClasses()->count())->toBe(0);
});

it('admin can record a winning entry on a judge-awarded trophy', function () {
    $judge = User::factory()->judge()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $judge->id]);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => $trophy->name,
            'is_points_based' => '0',
            'judge_id' => $judge->id,
            'winning_entry_number' => $entry->entry_number,
        ])
        ->assertRedirect(route('admin.trophies.index'));

    expect($trophy->fresh()->winning_entry_id)->toBe($entry->id);
});

it('rejects an unknown winning entry number', function () {
    $judge = User::factory()->judge()->create();
    $trophy = Trophy::factory()->judgeAwarded()->create(['judge_id' => $judge->id]);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => $trophy->name,
            'is_points_based' => '0',
            'judge_id' => $judge->id,
            'winning_entry_number' => 999,
        ])
        ->assertSessionHasErrors('winning_entry_number');

    expect($trophy->fresh()->winning_entry_id)->toBeNull();
});

it('trophy index shows the winning exhibitor for a judge-awarded trophy', function () {
    $judge = User::factory()->judge()->create();
    $class = ShowClass::factory()->create();
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Carol', 'last_name' => 'White', 'full_name' => 'Carol White', 'sort_name' => 'White, Carol']);
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Trophy::factory()->judgeAwarded()->create([
        'name' => 'Judge Award',
        'judge_id' => $judge->id,
        'winning_entry_id' => $entry->id,
    ]);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee('Carol White');
});

it('admin can create a trophy with eligibility restrictions', function () {
    $class = ShowClass::factory()->create();

    $this->actingAs(trophyAdmin())
        ->post(route('admin.trophies.store'), [
            'name' => 'Resident Trophy',
            'class_ids' => [$class->id],
            'restrictions' => ['resident', 'novice'],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $trophy = Trophy::where('name', 'Resident Trophy')->first();
    $restrictions = DB::table('trophy_restrictions')->where('trophy_id', $trophy->id)->pluck('restriction')->toArray();
    expect($restrictions)->toContain('resident')->toContain('novice');
});

it('admin can update a trophy to add restrictions', function () {
    $class = ShowClass::factory()->create();
    $trophy = Trophy::factory()->create();
    $trophy->showClasses()->attach($class->id);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => $trophy->name,
            'class_ids' => [$class->id],
            'restrictions' => ['junior'],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    $restrictions = DB::table('trophy_restrictions')->where('trophy_id', $trophy->id)->pluck('restriction')->toArray();
    expect($restrictions)->toContain('junior');
});

it('admin can update a trophy to remove all restrictions', function () {
    $class = ShowClass::factory()->create();
    $trophy = Trophy::factory()->create();
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'resident']);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => $trophy->name,
            'class_ids' => [$class->id],
        ])
        ->assertRedirect(route('admin.trophies.index'));

    expect(DB::table('trophy_restrictions')->where('trophy_id', $trophy->id)->count())->toBe(0);
});

it('restrictions are cleared when trophy is changed to judge-awarded', function () {
    $judge = User::factory()->judge()->create();
    $class = ShowClass::factory()->create();
    $trophy = Trophy::factory()->pointsBased()->create();
    $trophy->showClasses()->attach($class->id);
    DB::table('trophy_restrictions')->insert(['trophy_id' => $trophy->id, 'restriction' => 'resident']);

    $this->actingAs(trophyAdmin())
        ->put(route('admin.trophies.update', $trophy), [
            'name' => $trophy->name,
            'is_points_based' => '0',
            'judge_id' => $judge->id,
        ])
        ->assertRedirect(route('admin.trophies.index'));

    expect(DB::table('trophy_restrictions')->where('trophy_id', $trophy->id)->count())->toBe(0);
});

it('trophy index lists all tied winners', function () {
    $trophy = Trophy::factory()->create(['name' => 'Tied Trophy']);
    $class = ShowClass::factory()->create(['max_entries_per_exhibitor' => 20]);
    $trophy->showClasses()->attach($class->id);

    $exhibitor1 = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Adams']);
    $exhibitor2 = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Brown']);

    $entry1 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor2->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '1st']);

    $this->actingAs(trophyAdmin())
        ->get(route('admin.trophies.index'))
        ->assertOk()
        ->assertSee($exhibitor1->full_name)
        ->assertSee($exhibitor2->full_name);
});

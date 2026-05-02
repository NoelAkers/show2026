<?php

use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function judgeAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can view the judge list', function () {
    User::factory()->judge()->create(['name' => 'Judge One']);

    $this->actingAs(judgeAdmin())
        ->get(route('admin.judges.index'))
        ->assertOk()
        ->assertSee('Judge One');
});

it('judge list shows assigned section count', function () {
    $section = ShowSection::factory()->create();
    $judge = User::factory()->judge()->create(['name' => 'Judge Two']);
    $judge->assignedSections()->attach($section);

    $this->actingAs(judgeAdmin())
        ->get(route('admin.judges.index'))
        ->assertOk()
        ->assertSee('Judge Two');
});

it('admin can create a judge account', function () {
    $this->actingAs(judgeAdmin())
        ->post(route('admin.judges.store'), [
            'name' => 'New Judge',
            'email' => 'newjudge@example.com',
        ])
        ->assertRedirect(route('admin.judges.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'New Judge',
        'email' => 'newjudge@example.com',
        'role' => 'judge',
    ]);
});

it('created judge has role judge', function () {
    $this->actingAs(judgeAdmin())
        ->post(route('admin.judges.store'), [
            'name' => 'A Judge',
            'email' => 'ajudge@example.com',
        ]);

    expect(User::where('email', 'ajudge@example.com')->first()->role)->toBe('judge');
});

it('judge email must be unique', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs(judgeAdmin())
        ->post(route('admin.judges.store'), [
            'name' => 'Someone',
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('admin can assign sections when creating a judge', function () {
    $section1 = ShowSection::factory()->create();
    $section2 = ShowSection::factory()->create();

    $this->actingAs(judgeAdmin())
        ->post(route('admin.judges.store'), [
            'name' => 'Multi Judge',
            'email' => 'multi@example.com',
            'section_ids' => [$section1->id, $section2->id],
        ])
        ->assertRedirect(route('admin.judges.index'));

    $judge = User::where('email', 'multi@example.com')->first();
    expect($judge->assignedSections()->count())->toBe(2);
});

it('admin can update judge details', function () {
    $judge = User::factory()->judge()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs(judgeAdmin())
        ->put(route('admin.judges.update', $judge), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertRedirect(route('admin.judges.index'));

    expect($judge->fresh()->name)->toBe('New Name')
        ->and($judge->fresh()->email)->toBe('new@example.com');
});

it('admin can update section assignments', function () {
    $section1 = ShowSection::factory()->create();
    $section2 = ShowSection::factory()->create();
    $judge = User::factory()->judge()->create();
    $judge->assignedSections()->attach($section1);

    $this->actingAs(judgeAdmin())
        ->put(route('admin.judges.update', $judge), [
            'name' => $judge->name,
            'email' => $judge->email,
            'section_ids' => [$section2->id],
        ])
        ->assertRedirect(route('admin.judges.index'));

    $assignedIds = $judge->fresh()->assignedSections()->pluck('show_sections.id')->toArray();
    expect($assignedIds)->toContain($section2->id)
        ->and($assignedIds)->not->toContain($section1->id);
});

it('admin can remove all section assignments', function () {
    $section = ShowSection::factory()->create();
    $judge = User::factory()->judge()->create();
    $judge->assignedSections()->attach($section);

    $this->actingAs(judgeAdmin())
        ->put(route('admin.judges.update', $judge), [
            'name' => $judge->name,
            'email' => $judge->email,
        ])
        ->assertRedirect(route('admin.judges.index'));

    expect($judge->fresh()->assignedSections()->count())->toBe(0);
});

it('admin can delete a judge', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs(judgeAdmin())
        ->delete(route('admin.judges.destroy', $judge))
        ->assertRedirect(route('admin.judges.index'));

    $this->assertDatabaseMissing('users', ['id' => $judge->id]);
});

it('guest is redirected from judge routes', function () {
    $this->get(route('admin.judges.index'))
        ->assertRedirect(route('login'));
});

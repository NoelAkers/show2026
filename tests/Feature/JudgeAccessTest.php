<?php

use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('judge is redirected to judge sections after login', function () {
    $judge = User::factory()->judge()->create();

    $this->post(route('login'), [
        'email' => $judge->email,
        'password' => 'password',
    ])->assertRedirect(route('judge.sections.index'));
});

it('admin is redirected to dashboard after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));
});

it('judge sees only their assigned sections', function () {
    $judge = User::factory()->judge()->create();
    $assigned = ShowSection::factory()->create(['name' => 'Assigned Section']);
    $other = ShowSection::factory()->create(['name' => 'Other Section']);

    $judge->assignedSections()->attach($assigned->id);

    $this->actingAs($judge)
        ->get(route('judge.sections.index'))
        ->assertOk()
        ->assertSee('Assigned Section')
        ->assertDontSee('Other Section');
});

it('judge sees empty state when assigned to no sections', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs($judge)
        ->get(route('judge.sections.index'))
        ->assertOk()
        ->assertSee('No sections assigned');
});

it('judge receives 403 on admin show-sections route', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs($judge)
        ->get(route('admin.show-sections.index'))
        ->assertForbidden();
});

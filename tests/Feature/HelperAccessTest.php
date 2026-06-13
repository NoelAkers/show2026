<?php

use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('helper can access the exhibitor list', function () {
    $helper = User::factory()->helper()->create();

    $this->actingAs($helper)
        ->get(route('helper.exhibitors.index'))
        ->assertOk();
});

it('helper sees exhibitors ordered by sort name', function () {
    $helper = User::factory()->helper()->create();
    Exhibitor::factory()->create(['first_name' => 'Zara', 'last_name' => 'Adams', 'full_name' => 'Zara Adams', 'sort_name' => 'Adams, Zara']);
    Exhibitor::factory()->create(['first_name' => 'Ben', 'last_name' => 'Zeta', 'full_name' => 'Ben Zeta', 'sort_name' => 'Zeta, Ben']);

    $response = $this->actingAs($helper)
        ->get(route('helper.exhibitors.index'))
        ->assertOk();

    $content = $response->getContent();
    expect(strpos($content, 'Adams'))->toBeLessThan(strpos($content, 'Zeta'));
});

it('helper can access the add-entry page for an exhibitor', function () {
    $helper = User::factory()->helper()->create();
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs($helper)
        ->get(route('helper.exhibitors.add-entry', $exhibitor))
        ->assertOk();
});

it('admin can access helper exhibitor list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('helper.exhibitors.index'))
        ->assertOk();
});

it('admin can access helper add-entry page', function () {
    $admin = User::factory()->admin()->create();
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs($admin)
        ->get(route('helper.exhibitors.add-entry', $exhibitor))
        ->assertOk();
});

it('judge cannot access helper routes', function () {
    $judge = User::factory()->judge()->create();

    $this->actingAs($judge)
        ->get(route('helper.exhibitors.index'))
        ->assertForbidden();
});

it('guest is redirected to login on helper routes', function () {
    $this->get(route('helper.exhibitors.index'))
        ->assertRedirect(route('login'));
});

it('helper is redirected to exhibitor list after login', function () {
    $helper = User::factory()->helper()->create();

    $this->post(route('login'), [
        'email' => $helper->email,
        'password' => 'password',
    ])->assertRedirect(route('helper.exhibitors.index'));
});

it('helper exhibitor index shows names as links not buttons', function () {
    $helper = User::factory()->helper()->create();
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe']);

    $content = $this->actingAs($helper)
        ->get(route('helper.exhibitors.index'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain(route('helper.exhibitors.add-entry', $exhibitor))
        ->not->toContain('Add Entries');
});

it('helper add-entry page does not show print labels button', function () {
    $helper = User::factory()->helper()->create();
    $exhibitor = Exhibitor::factory()->create();

    $content = $this->actingAs($helper)
        ->get(route('helper.exhibitors.add-entry', $exhibitor))
        ->assertOk()
        ->getContent();

    expect($content)->not->toContain('Print All Labels');
});

it('helper add-entry page back button links to helper exhibitor list', function () {
    $helper = User::factory()->helper()->create();
    $exhibitor = Exhibitor::factory()->create();

    $content = $this->actingAs($helper)
        ->get(route('helper.exhibitors.add-entry', $exhibitor))
        ->assertOk()
        ->getContent();

    expect($content)->toContain(route('helper.exhibitors.index'));
});

<?php

use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helpers
function adminUser(): User
{
    return User::factory()->admin()->create();
}

function judgeUser(): User
{
    return User::factory()->judge()->create();
}

it('admin can view sections list', function () {
    $section = ShowSection::factory()->create(['name' => 'Vegetables']);

    $this->actingAs(adminUser())
        ->get(route('admin.show-sections.index'))
        ->assertOk()
        ->assertSee('Vegetables');
});

it('admin can create a section with valid data', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.show-sections.store'), [
            'name' => 'Flowers',
            'description' => 'All flower classes',
            'sort_order' => 1,
        ])
        ->assertRedirect(route('admin.show-sections.index'));

    $this->assertDatabaseHas('show_sections', ['name' => 'Flowers']);
});

it('creating a section with a duplicate name fails validation', function () {
    ShowSection::factory()->create(['name' => 'Flowers']);

    $this->actingAs(adminUser())
        ->post(route('admin.show-sections.store'), [
            'name' => 'Flowers',
            'sort_order' => 1,
        ])
        ->assertSessionHasErrors('name');
});

it('admin can update an existing section', function () {
    $section = ShowSection::factory()->create(['name' => 'Old Name']);

    $this->actingAs(adminUser())
        ->put(route('admin.show-sections.update', $section), [
            'name' => 'New Name',
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.show-sections.index'));

    expect($section->fresh()->name)->toBe('New Name');
});

it('admin can delete a section with no classes', function () {
    $section = ShowSection::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('admin.show-sections.destroy', $section))
        ->assertRedirect(route('admin.show-sections.index'));

    $this->assertDatabaseMissing('show_sections', ['id' => $section->id]);
});

it('admin cannot delete a section that has classes', function () {
    $section = ShowSection::factory()->create();
    ShowClass::factory()->create(['show_section_id' => $section->id]);

    $this->actingAs(adminUser())
        ->delete(route('admin.show-sections.destroy', $section))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('show_sections', ['id' => $section->id]);
});

it('guest is redirected to login on section index', function () {
    $this->get(route('admin.show-sections.index'))->assertRedirect(route('login'));
});

it('guest is redirected to login on section create', function () {
    $this->get(route('admin.show-sections.create'))->assertRedirect(route('login'));
});

it('guest is redirected to login on section store', function () {
    $this->post(route('admin.show-sections.store'), [])->assertRedirect(route('login'));
});

it('judge receives 403 on section index', function () {
    $this->actingAs(judgeUser())
        ->get(route('admin.show-sections.index'))
        ->assertForbidden();
});

it('judge receives 403 on section store', function () {
    $this->actingAs(judgeUser())
        ->post(route('admin.show-sections.store'), [])
        ->assertForbidden();
});

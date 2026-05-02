<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function exhibitorAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can view the exhibitor list', function () {
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);

    $this->actingAs(exhibitorAdmin())
        ->get(route('admin.exhibitors.index'))
        ->assertOk()
        ->assertSee('Alice Smith');
});

it('admin can search exhibitors by name', function () {
    Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);
    Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);

    $this->actingAs(exhibitorAdmin())
        ->get(route('admin.exhibitors.index', ['search' => 'Alice']))
        ->assertOk()
        ->assertSee('Alice Smith')
        ->assertDontSee('Bob Jones');
});

it('admin can filter exhibitors by type', function () {
    Exhibitor::factory()->adult()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);
    Exhibitor::factory()->junior()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);

    $this->actingAs(exhibitorAdmin())
        ->get(route('admin.exhibitors.index', ['type' => 'junior']))
        ->assertOk()
        ->assertSee('Bob Jones')
        ->assertDontSee('Alice Smith');
});

it('admin can create an exhibitor', function () {
    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'type' => 'adult',
            'is_resident' => '1',
        ])
        ->assertRedirect(route('admin.exhibitors.index'));

    $this->assertDatabaseHas('exhibitors', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'full_name' => 'Jane Doe',
        'sort_name' => 'Doe, Jane',
    ]);
});

it('admin can view an exhibitor', function () {
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);

    $this->actingAs(exhibitorAdmin())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee('Alice Smith');
});

it('admin can update an exhibitor', function () {
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Old', 'last_name' => 'Name', 'full_name' => 'Old Name', 'sort_name' => 'Name, Old']);

    $this->actingAs(exhibitorAdmin())
        ->put(route('admin.exhibitors.update', $exhibitor), [
            'first_name' => 'New',
            'last_name' => 'Name',
            'type' => 'adult',
            'is_resident' => '1',
        ])
        ->assertRedirect(route('admin.exhibitors.show', $exhibitor));

    expect($exhibitor->fresh()->first_name)->toBe('New')
        ->and($exhibitor->fresh()->full_name)->toBe('New Name');
});

it('admin can delete an exhibitor with no entries', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(exhibitorAdmin())
        ->delete(route('admin.exhibitors.destroy', $exhibitor))
        ->assertRedirect(route('admin.exhibitors.index'));

    $this->assertDatabaseMissing('exhibitors', ['id' => $exhibitor->id]);
});

it('admin cannot delete an exhibitor who has entries', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(exhibitorAdmin())
        ->delete(route('admin.exhibitors.destroy', $exhibitor))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('exhibitors', ['id' => $exhibitor->id]);
});

it('admin can mark an exhibitor as paid', function () {
    $exhibitor = Exhibitor::factory()->create(['has_paid' => false]);

    $this->actingAs(exhibitorAdmin())
        ->patch(route('admin.exhibitors.mark-paid', $exhibitor))
        ->assertRedirect();

    expect($exhibitor->fresh()->has_paid)->toBeTrue();
});

it('admin can mark an exhibitor as unpaid', function () {
    $exhibitor = Exhibitor::factory()->create(['has_paid' => true]);

    $this->actingAs(exhibitorAdmin())
        ->patch(route('admin.exhibitors.mark-unpaid', $exhibitor))
        ->assertRedirect();

    expect($exhibitor->fresh()->has_paid)->toBeFalse();
});

it('show page displays fee summary correctly', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(3)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(exhibitorAdmin())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee('3')      // total entries
        ->assertSee('£1.50'); // 3 × 50p = £1.50
});

it('guest is redirected from exhibitor index', function () {
    $this->get(route('admin.exhibitors.index'))
        ->assertRedirect(route('login'));
});

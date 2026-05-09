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
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create(['has_paid' => false, 'amount_paid_pence' => 0]);
    Entry::factory()->count(2)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(exhibitorAdmin())
        ->patch(route('admin.exhibitors.mark-paid', $exhibitor))
        ->assertRedirect();

    $fresh = $exhibitor->fresh();
    expect($fresh->has_paid)->toBeTrue()
        ->and($fresh->amount_paid_pence)->toBe($exhibitor->feeOwedPence());
});

it('admin can mark an exhibitor as unpaid', function () {
    $exhibitor = Exhibitor::factory()->create(['has_paid' => true, 'amount_paid_pence' => 100]);

    $this->actingAs(exhibitorAdmin())
        ->patch(route('admin.exhibitors.mark-unpaid', $exhibitor))
        ->assertRedirect();

    $fresh = $exhibitor->fresh();
    expect($fresh->has_paid)->toBeFalse()
        ->and($fresh->amount_paid_pence)->toBe(0);
});

it('show page displays fee summary correctly', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(3)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(exhibitorAdmin())
        ->get(route('admin.exhibitors.show', $exhibitor))
        ->assertOk()
        ->assertSee('3')         // total entries
        ->assertSee('£1.50')     // fee owed: 3 × 50p = £1.50
        ->assertSee('Amount Paid')
        ->assertSee('Balance');
});

it('admin can update amount paid for an exhibitor', function () {
    $exhibitor = Exhibitor::factory()->create(['amount_paid_pence' => 0]);

    $this->actingAs(exhibitorAdmin())
        ->patch(route('admin.exhibitors.update-payment', $exhibitor), ['amount_paid_pence' => 75])
        ->assertRedirect();

    expect($exhibitor->fresh()->amount_paid_pence)->toBe(75);
});

it('amount_paid_pence must be a non-negative integer', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(exhibitorAdmin())
        ->patch(route('admin.exhibitors.update-payment', $exhibitor), ['amount_paid_pence' => -10])
        ->assertSessionHasErrors('amount_paid_pence');
});

it('balance is zero when amount paid equals fee owed', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create(['amount_paid_pence' => 0]);
    Entry::factory()->count(2)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $exhibitor->update(['amount_paid_pence' => $exhibitor->feeOwedPence()]);

    expect($exhibitor->balancePence())->toBe(0);
});

it('balance is negative when overpaid', function () {
    $exhibitor = Exhibitor::factory()->adult()->create(['amount_paid_pence' => 500]);

    // no entries, so fee owed = 0; 500 pence paid = refund owed
    expect($exhibitor->balancePence())->toBe(-500);
});

it('exhibitor defaults to novice on creation', function () {
    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'type' => 'adult',
        ])
        ->assertRedirect(route('admin.exhibitors.index'));

    $this->assertDatabaseHas('exhibitors', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'is_novice' => true,
    ]);
});

it('admin can set an exhibitor as not a novice', function () {
    $exhibitor = Exhibitor::factory()->novice()->create(['first_name' => 'Old', 'last_name' => 'Name', 'full_name' => 'Old Name', 'sort_name' => 'Name, Old']);

    $this->actingAs(exhibitorAdmin())
        ->put(route('admin.exhibitors.update', $exhibitor), [
            'first_name' => 'Old',
            'last_name' => 'Name',
            'type' => 'adult',
            'is_novice' => '0',
        ])
        ->assertRedirect(route('admin.exhibitors.show', $exhibitor));

    expect($exhibitor->fresh()->is_novice)->toBeFalse();
});

it('guest is redirected from exhibitor index', function () {
    $this->get(route('admin.exhibitors.index'))
        ->assertRedirect(route('login'));
});

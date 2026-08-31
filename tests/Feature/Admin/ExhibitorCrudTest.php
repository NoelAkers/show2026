<?php

use App\Enums\TransactionType;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\PrizeLevel;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\Transaction;
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

it('admin can create an exhibitor with an email address', function () {
    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'type' => 'adult',
        ])
        ->assertRedirect(route('admin.exhibitors.index'));

    $this->assertDatabaseHas('exhibitors', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.doe@example.com',
    ]);
});

it('exhibitor email is optional when created by admin', function () {
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
        'email' => null,
    ]);
});

it('exhibitor email must be a valid email address', function (string $invalidEmail) {
    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => $invalidEmail,
            'type' => 'adult',
        ])
        ->assertSessionHasErrors('email');
})->with([
    'not-an-email',
    'jane@doe', // no TLD — accepted by Laravel's default RFC email validator, so must reject explicitly
]);

it('rejects an invalidly formatted email address on update, without overwriting the existing valid one', function () {
    $exhibitor = Exhibitor::factory()->create(['email' => 'original@example.com']);

    $this->actingAs(exhibitorAdmin())
        ->put(route('admin.exhibitors.update', $exhibitor), [
            'first_name' => $exhibitor->first_name,
            'last_name' => $exhibitor->last_name,
            'email' => 'jane@doe', // no TLD
            'type' => 'adult',
        ])
        ->assertSessionHasErrors('email');

    expect($exhibitor->fresh()->email)->toBe('original@example.com');
});

it('admin can update an exhibitor email address', function () {
    $exhibitor = Exhibitor::factory()->create(['email' => null]);

    $this->actingAs(exhibitorAdmin())
        ->put(route('admin.exhibitors.update', $exhibitor), [
            'first_name' => $exhibitor->first_name,
            'last_name' => $exhibitor->last_name,
            'email' => 'updated@example.com',
            'type' => 'adult',
        ])
        ->assertRedirect(route('admin.exhibitors.show', $exhibitor));

    expect($exhibitor->fresh()->email)->toBe('updated@example.com');
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

it('admin can record a cash receipt for an exhibitor', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(2)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'amount_pounds' => number_format($exhibitor->feeOwedPence() / 100, 2, '.', ''),
            'type' => 'cash_receipt',
        ])
        ->assertRedirect();

    $fresh = $exhibitor->fresh();
    expect($fresh->hasPaid())->toBeTrue()
        ->and($fresh->amountPaidPence())->toBe($exhibitor->feeOwedPence());

    $this->assertDatabaseHas('transactions', [
        'exhibitor_id' => $exhibitor->id,
        'amount_pence' => $exhibitor->feeOwedPence(),
        'type' => TransactionType::CashReceipt->value,
    ]);
});

it('recording a cash payment reduces the amount paid, e.g. for a winnings payout', function () {
    $prizeLevel = PrizeLevel::factory()->create(['first_place_pence' => 300]);
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    // exhibitor pays fee owed in cash, then the show pays out their winnings in cash
    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'amount_pounds' => number_format($exhibitor->feeOwedPence() / 100, 2, '.', ''),
            'type' => 'cash_receipt',
        ])
        ->assertRedirect();

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'amount_pounds' => '3.00',
            'type' => 'cash_payment',
        ])
        ->assertRedirect();

    $fresh = $exhibitor->fresh();
    expect($fresh->amountPaidPence())->toBe($exhibitor->feeOwedPence() - 300)
        ->and($fresh->balancePence())->toBe(0);
});

it('amount_pounds must be a positive number when recording a transaction', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'amount_pounds' => '-10',
            'type' => 'cash_receipt',
        ])
        ->assertSessionHasErrors('amount_pounds');
});

it('recording a cash receipt with no amount defaults to the outstanding fee', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(2)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'type' => 'cash_receipt',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'exhibitor_id' => $exhibitor->id,
        'amount_pence' => $exhibitor->feeOwedPence(),
        'type' => TransactionType::CashReceipt->value,
    ]);
});

it('recording a cash receipt with no amount nets off winnings due against the fee owed', function () {
    $prizeLevel = PrizeLevel::factory()->create(['first_place_pence' => 300]);
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(6)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    // 7 entries owed at the fee rate, minus 300p of winnings due
    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'type' => 'cash_receipt',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'exhibitor_id' => $exhibitor->id,
        'amount_pence' => $exhibitor->feeOwedPence() - 300,
        'type' => TransactionType::CashReceipt->value,
    ]);
});

it('recording a payout with no amount defaults to the amount owed to the exhibitor', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 700]);

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'type' => 'cash_payment',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'exhibitor_id' => $exhibitor->id,
        'amount_pence' => 700,
        'type' => TransactionType::CashPayment->value,
    ]);
});

it('recording a transaction with no amount and nothing due records no transaction', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'type' => 'cash_receipt',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseCount('transactions', 0);
});

it('type must be a valid transaction type when recording a transaction', function () {
    $exhibitor = Exhibitor::factory()->create();

    $this->actingAs(exhibitorAdmin())
        ->post(route('admin.exhibitors.transactions.store', $exhibitor), [
            'amount_pounds' => '10.00',
            'type' => 'bitcoin',
        ])
        ->assertSessionHasErrors('type');
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
        ->assertSee('Amount received from exhibitor')
        ->assertSee('Balance due to exhibitor');
});

it('balance is zero when amount paid equals fee owed', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(2)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => $exhibitor->feeOwedPence()]);

    expect($exhibitor->balancePence())->toBe(0);
});

it('balance is negative when overpaid', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 500]);

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

it('unchecking is_resident and is_novice on update saves false', function () {
    $exhibitor = Exhibitor::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'full_name' => 'Jane Doe',
        'sort_name' => 'Doe, Jane',
        'is_resident' => true,
        'is_novice' => true,
    ]);

    $this->actingAs(exhibitorAdmin())
        ->put(route('admin.exhibitors.update', $exhibitor), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'type' => 'adult',
            // is_resident and is_novice omitted — simulates unchecked checkboxes
        ])
        ->assertRedirect();

    expect($exhibitor->fresh()->is_resident)->toBeFalse()
        ->and($exhibitor->fresh()->is_novice)->toBeFalse();
});

it('guest is redirected from exhibitor index', function () {
    $this->get(route('admin.exhibitors.index'))
        ->assertRedirect(route('login'));
});

it('winningsPence returns 0 when exhibitor has no placed results', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->for($exhibitor)->create();

    expect($exhibitor->winningsPence())->toBe(0);
});

it('winningsPence sums prize amounts for placed results', function () {
    $prizeLevel = PrizeLevel::factory()->create([
        'first_place_pence' => 300,
        'second_place_pence' => 150,
        'third_place_pence' => 75,
    ]);
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create();
    $exhibitor = Exhibitor::factory()->adult()->create();

    $entry1 = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry1)->create(['placement' => '1st']);

    $entry2 = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry2)->create(['placement' => '3rd']);

    expect($exhibitor->winningsPence())->toBe(375);
});

it('winningsPence returns 0 for highly_commended placement', function () {
    $prizeLevel = PrizeLevel::factory()->create([
        'first_place_pence' => 300,
        'second_place_pence' => 150,
        'third_place_pence' => 75,
    ]);
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create();
    $exhibitor = Exhibitor::factory()->adult()->create();
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => 'highly_commended']);

    expect($exhibitor->winningsPence())->toBe(0);
});

it('balance is reduced by winnings', function () {
    $prizeLevel = PrizeLevel::factory()->create([
        'first_place_pence' => 200,
        'second_place_pence' => 100,
        'third_place_pence' => 50,
    ]);
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create();
    $exhibitor = Exhibitor::factory()->adult()->create();
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    // feeOwedPence = 1 × 50p = 50, winnings = 200, amount_paid = 0 → balance = -150
    expect($exhibitor->balancePence())->toBe(50 - 200);
});

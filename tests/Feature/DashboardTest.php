<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('dashboard is accessible to admin', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSuccessful();
});

test('judge accessing dashboard is redirected to judge sections', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('judge.sections.index'));
});

test('correct section count is displayed', function () {
    ShowSection::factory()->count(3)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('3');
});

test('correct class count is displayed', function () {
    $section = ShowSection::factory()->create();
    ShowClass::factory()->for($section)->count(4)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('4');
});

test('correct exhibitor count with adult and junior split is displayed', function () {
    Exhibitor::factory()->adult()->count(5)->create();
    Exhibitor::factory()->junior()->count(2)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('5 adult')
        ->assertSee('2 junior');
});

test('correct entry count is displayed', function () {
    Entry::factory()->count(6)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('6');
});

test('correct results entered and outstanding counts are displayed', function () {
    $entries = Entry::factory()->count(3)->create();
    Result::factory()->for($entries->first())->create(['placement' => '1st']);
    Result::factory()->for($entries->get(1))->create(['placement' => null]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('1')
        ->assertSee('1 outstanding');
});

test('correct class counts are displayed', function () {
    $section = ShowSection::factory()->create();
    ShowClass::factory()->for($section)->create();
    $judgedClass = ShowClass::factory()->for($section)->create();
    $awaitingClass = ShowClass::factory()->for($section)->create();

    Entry::factory()->for($judgedClass)->has(Result::factory())->create();
    Entry::factory()->for($awaitingClass)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('3')
        ->assertSee('2 with entries')
        ->assertSee('1 awaiting judging');
});

test('correct paid and unpaid exhibitor count is displayed', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($section)->create();

    $paidExhibitors = Exhibitor::factory()->adult()->count(3)->create();
    $paidExhibitors->each(function (Exhibitor $exhibitor) use ($class) {
        Entry::factory()->for($class)->for($exhibitor)->create();
        Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => $exhibitor->feeOwedPence()]);
    });

    $unpaidExhibitors = Exhibitor::factory()->adult()->count(2)->create();
    $unpaidExhibitors->each(fn (Exhibitor $exhibitor) => Entry::factory()->for($class)->for($exhibitor)->create());

    Exhibitor::factory()->junior()->count(4)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('3')
        ->assertSee('2 unpaid');
});

test('correct total received and due amounts are displayed', function () {
    // has overpaid/paid in full: fee owed £0, £10.00 paid, so no balance due
    $paidExhibitor = Exhibitor::factory()->adult()->create();
    Transaction::factory()->cashReceipt()->for($paidExhibitor)->create(['amount_pence' => 1000]);

    // fee owed for 10 entries is £5.00 (10 × 50p), only £2.00 paid, so £3.00 due
    $owingExhibitor = Exhibitor::factory()->adult()->create();
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($section)->create();
    Entry::factory()->for($class)->for($owingExhibitor)->count(10)->create();
    Transaction::factory()->cardPayment()->for($owingExhibitor)->create(['amount_pence' => 200]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertSee('£12.00 received')
        ->assertSee('£3.00 due');
});

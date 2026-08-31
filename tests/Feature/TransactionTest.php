<?php

use App\Enums\TransactionType;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\PrizeLevel;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sums cash receipts and card payments as amount paid', function () {
    $exhibitor = Exhibitor::factory()->create();
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 300]);
    Transaction::factory()->cardPayment()->for($exhibitor)->create(['amount_pence' => 200]);

    expect($exhibitor->amountPaidPence())->toBe(500);
});

it('subtracts cash payments out from amount paid', function () {
    $exhibitor = Exhibitor::factory()->create();
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 500]);
    Transaction::factory()->cashPayment()->for($exhibitor)->create(['amount_pence' => 200]);

    expect($exhibitor->amountPaidPence())->toBe(300);
});

it('amount paid is zero for an exhibitor with no transactions', function () {
    $exhibitor = Exhibitor::factory()->create();

    expect($exhibitor->amountPaidPence())->toBe(0);
});

it('casts the transaction type to the TransactionType enum', function () {
    $transaction = Transaction::factory()->cardPayment()->create();

    expect($transaction->type)->toBe(TransactionType::CardPayment);
});

it('balance due to exhibitor is the inverse of balance owed by exhibitor', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 800]);

    // no entries, so fee owed = 0; 800 pence paid = refund owed to exhibitor
    expect($exhibitor->balancePence())->toBe(-800)
        ->and($exhibitor->balanceDueToExhibitorPence())->toBe(800);
});

it('outstanding from exhibitor is the fee owed less what has already been received', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(2)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 30]);

    expect($exhibitor->outstandingFromExhibitorPence())->toBe($exhibitor->feeOwedPence() - 30);
});

it('outstanding from exhibitor never goes below zero when overpaid', function () {
    $exhibitor = Exhibitor::factory()->adult()->create();
    Transaction::factory()->cashReceipt()->for($exhibitor)->create(['amount_pence' => 500]);

    expect($exhibitor->outstandingFromExhibitorPence())->toBe(0);
});

it('outstanding from exhibitor nets off winnings due, so a settling-up payment is fee owed less winnings', function () {
    $section = ShowSection::factory()->create();
    $prizeLevel = PrizeLevel::factory()->create(['first_place_pence' => 400]);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'prize_level_id' => $prizeLevel->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->count(10)->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    Result::factory()->for($exhibitor->entries()->first())->create(['placement' => '1st']);

    expect($exhibitor->feeOwedPence())->toBe(500)
        ->and($exhibitor->winningsPence())->toBe(400)
        ->and($exhibitor->outstandingFromExhibitorPence())->toBe(100);
});

it('owed to exhibitor is zero when the exhibitor owes the show', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->adult()->create();
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    expect($exhibitor->owedToExhibitorPence())->toBe(0);
});

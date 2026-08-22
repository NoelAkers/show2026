<?php

use App\Enums\TransactionType;
use App\Models\Exhibitor;
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

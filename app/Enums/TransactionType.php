<?php

namespace App\Enums;

enum TransactionType: string
{
    case CashReceipt = 'cash_receipt';
    case CashPayment = 'cash_payment';
    case CardPayment = 'card_payment';

    public function label(): string
    {
        return match ($this) {
            self::CashReceipt => 'From exhibitor (cash)',
            self::CardPayment => 'From exhibitor (card)',
            self::CashPayment => 'To exhibitor',
        };
    }
}

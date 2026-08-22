<?php

namespace App\Enums;

enum TransactionType: string
{
    case CashReceipt = 'cash_receipt';
    case CashPayment = 'cash_payment';
    case CardPayment = 'card_payment';
}

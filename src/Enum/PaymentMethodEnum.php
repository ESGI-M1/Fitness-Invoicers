<?php

namespace App\Enum;

enum PaymentMethodEnum: string
{
    case CREDIT_CARD = 'CREDIT_CARD';
    case DEBIT_CARD = 'DEBIT_CARD';
    case PAYPAL = 'PAYPAL';
    case APPLE_PAY = 'APPLE_PAY';
    case GOOGLE_PAY = 'GOOGLE_PAY';
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case CASH = 'CASH';
    case OTHER = 'OTHER';
}

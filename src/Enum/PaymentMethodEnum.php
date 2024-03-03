<?php

namespace App\Enum;

enum PaymentMethodEnum: string
{
    case CREDIT_CARD = 'CREDIT_CARD';
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case OTHER = 'OTHER';
}
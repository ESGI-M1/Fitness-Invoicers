<?php

namespace App\Enum;

enum PaymentStatusEnum: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case PARTIALLY_PAID = 'PARTIALLY_PAID';
}
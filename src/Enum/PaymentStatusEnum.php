<?php

namespace App\Enum;

enum PaymentStatusEnum: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case DECLINED = 'DECLINED';
    case REFUNDED = 'REFUNDED';
    case ERROR = 'ERROR';
}

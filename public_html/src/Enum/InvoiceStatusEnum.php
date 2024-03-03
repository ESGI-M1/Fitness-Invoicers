<?php

namespace App\Enum;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ARCHIVED = 'archived';
}

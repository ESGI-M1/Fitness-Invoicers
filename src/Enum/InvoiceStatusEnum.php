<?php

namespace App\Enum;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case ACCEPTED = 'accepted';
    case VALIDATED = 'validated';
    case SENT = 'sent';
    case ARCHIVED = 'archived';

    public function getStatusLabel(): string
    {
        return $this->value;
    }
}
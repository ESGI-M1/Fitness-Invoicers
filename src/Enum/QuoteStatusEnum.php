<?php

namespace App\Enum;

enum QuoteStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case ACCEPTED = 'accepted';
    case SENT = 'sent';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function getStatusLabel(): string
    {
        return $this->value;
    }
}
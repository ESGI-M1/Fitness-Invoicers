<?php

namespace App\Enum;

enum CustomerStatutEnum: string
{
    case DELETED = 'deleted';
    case VALIDATED = 'validated';

    public function getStatusLabel(): string
    {
        return $this->value;
    }
}
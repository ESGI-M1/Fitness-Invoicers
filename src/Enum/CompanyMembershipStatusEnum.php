<?php

namespace App\Enum;

enum CompanyMembershipStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REFUSED = 'refused';
}

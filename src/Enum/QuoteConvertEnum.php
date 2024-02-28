<?php

namespace App\Enum;

enum QuoteConvertEnum: string
{
    case WITHOUT_MODIFICATION = 'without_modification';

    case WITH_DISCOUNT = 'with_discount';

    case WITH_DEPOSIT = 'with_deposit';

    case WITH_DEPOSIT_PERCENTAGE = 'with_deposit_percentage';

}

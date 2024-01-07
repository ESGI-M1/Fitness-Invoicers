<?php

namespace App\Factory;

use App\Entity\Quote;
use App\Enum\QuoteStatusEnum;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Quote>
 */
final class QuoteFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'status' => self::faker()->randomElement(QuoteStatusEnum::class),
            'items' => [],
            'company' => CompanyFactory::createOne(),
        ];
    }

    protected static function getClass(): string
    {
        return Quote::class;
    }
}

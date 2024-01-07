<?php

namespace App\Factory;

use App\Entity\Invoice;
use App\Enum\InvoiceStatusEnum;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Invoice>
 */
final class InvoiceFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'status' => self::faker()->randomElement(InvoiceStatusEnum::class),
            'items' => [],
            'company' => CompanyFactory::createOne(),
        ];
    }

    protected static function getClass(): string
    {
        return Invoice::class;
    }
}

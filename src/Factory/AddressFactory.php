<?php

namespace App\Factory;

use App\Entity\Address;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Company>
 */
final class AddressFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'postal_code' => self::faker()->postcode(),
            'city' => self::faker()->city(),
            'country' => self::faker()->country(),
            'street' => self::faker()->streetAddress(),
        ];
    }

    protected static function getClass(): string
    {
        return Address::class;
    }
}

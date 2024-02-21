<?php

namespace App\Factory;

use App\Entity\Customer;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Customer>
 */
final class CustomerFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'firstName' => self::faker()->firstName,
            'lastName' => self::faker()->lastName,
            'deliveryaddress' => AddressFactory::createOne(),
            'billingAddress' => AddressFactory::createOne(),
        ];
    }

    protected static function getClass(): string
    {
        return Customer::class;
    }
}

<?php

namespace App\Factory;

use App\Entity\Item;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Item>
 */
final class ItemFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'quantity' => self::faker()->numberBetween(1, 10),
            'productLabel' => self::faker()->word(),
            'productRef' => self::faker()->uuid(),
            'productPrice' => self::faker()->randomFloat(2, 0, 1000),
            'product' => ProductFactory::createOne(),
        ];
    }

    protected static function getClass(): string
    {
        return Item::class;
    }
}

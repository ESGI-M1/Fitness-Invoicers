<?php

namespace App\Factory;

use App\Entity\Product;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Product>
 */
final class ProductFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'categories' => [],
            'name' => self::faker()->words(random_int(1, 3), true),
            'price' => self::faker()->randomFloat(),
            'reference' => self::faker()->uuid(),
        ];
    }

    protected static function getClass(): string
    {
        return Product::class;
    }
}

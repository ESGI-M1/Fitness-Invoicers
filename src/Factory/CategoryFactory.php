<?php

namespace App\Factory;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Category>
 */
final class CategoryFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'company' => CompanyFactory::new(),
            'name' => self::faker()->words(random_int(1, 3), true),
            'products' => [],
        ];
    }

    protected static function getClass(): string
    {
        return Category::class;
    }
}

<?php

namespace App\Builder;

use App\Entity\Category;
use App\Factory\ProductFactory;

class ProductBuilder implements BuilderInterface
{
    private ?string $name = null;
    private ?float $price = null;
    /*
     * @var array<Category>|null
     */
    private ?array $categories = null;

    public function build(bool $persist = true): object
    {
        $product = ProductFactory::createOne(array_filter([
            'name' => $this->name,
            'price' => $this->price,
            'categories' => $this->categories,
        ]));

        if ($persist) {
            $product->save();
        }

        return $product->object();
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /*
     * @param array<Category> $categories
     */
    public function withCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function inCategory(Category $category): self
    {
        $this->categories[] = $category;

        return $this;
    }
}

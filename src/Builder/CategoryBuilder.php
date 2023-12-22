<?php

namespace App\Builder;

use App\Entity\Company;
use App\Factory\CategoryFactory;

class CategoryBuilder implements BuilderInterface
{
    private ?string $name = null;
    /*
     * @var array<Product>|null
     */
    private ?array $products = null;
    private ?Company $company = null;

    public function build(bool $persist = true): object
    {
        $category = CategoryFactory::createOne(array_filter([
            'name' => $this->name,
            'products' => $this->products,
            'company' => $this->company,
        ]));

        if ($persist) {
            $category->save();
        }

        return $category->object();
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /*
     * @param array<Product> $products
     */
    public function withProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    public function withCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
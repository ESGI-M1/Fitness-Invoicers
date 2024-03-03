<?php

namespace App\Builder;

use App\Entity\Company;
use App\Entity\Address;
use App\Factory\CategoryFactory;

class AddressBuilder implements BuilderInterface
{
    private ?string $postalCode = null;
    private ?string $city = null;
    private ?string $country = null;
    private ?string $street = null;

    /**
     * @var array<Product>|null
     */
    private ?array $products = null;

    public function build(bool $persist = true): object
    {
        $address = AddressFactory::createOne(array_filter([
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'street' => $this->street,
        ]));

        if ($persist) {
            $address->save();
        }

        return $address->object();
    }

    public function withPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function withCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function withCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function withStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }
}

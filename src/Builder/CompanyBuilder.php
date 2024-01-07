<?php

namespace App\Builder;

use App\Entity\User;
use App\Factory\CompanyFactory;

class CompanyBuilder implements BuilderInterface
{
    private ?string $name = null;
    private ?string $siret = null;
    /*
     * @var array<CompanyMembership>|null
     */
    private ?array $companyMemberships = null;
    /*
     * @var array<Category>|null
     */
    private ?array $categories = null;
    private ?User $referent = null;

    public function build(bool $persist = true): object
    {
        $company = CompanyFactory::createOne(array_filter([
            'name' => $this->name,
            'siret' => $this->siret,
            'categories' => $this->categories,
            'referent' => $this->referent,
            'companyMemberships' => $this->companyMemberships,
        ]));

        if ($persist) {
            $company->save();
        }

        return $company->object();
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withSiret(string $siret): self
    {
        $this->siret = $siret;

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

    public function withReferent(User $referent): self
    {
        $this->referent = $referent;

        return $this;
    }

    /*
     * @param array<CompanyMembership> $companyMemberships
     */

    public function withCompanyMemberships(array $companyMemberships): self
    {
        $this->companyMemberships = $companyMemberships;

        return $this;
    }
}

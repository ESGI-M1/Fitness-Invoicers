<?php

namespace App\Builder;

use App\Entity\Category;
use App\Entity\CompanyMembership;
use App\Entity\Invoice;
use App\Entity\Quote;
use App\Entity\User;
use App\Factory\CompanyFactory;

class CompanyBuilder implements BuilderInterface
{
    private ?string $name = null;
    private ?string $siret = null;
    private ?User $referent = null;

    /**
     * @var array<CompanyMembership>|null
     */
    private ?array $companyMemberships = null;

    /**
     * @var array<Category>|null
     */
    private ?array $categories = null;

    /**
     * @var array<Invoice>|null
     */
    private ?array $invoices = null;

    /**
     * @var array<Quote>|null
     */
    private ?array $quotes = null;

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

    public function withReferent(User $referent): self
    {
        $this->referent = $referent;

        return $this;
    }

    /**
     * @param array<Category> $categories
     */
    public function withCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function addCategory(Category $category): self
    {
        $this->categories = [...($this->categories ?? []), $category];

        return $this;
    }

    /**
     * @param array<CompanyMembership> $companyMemberships
     */
    public function withCompanyMemberships(array $companyMemberships): self
    {
        $this->companyMemberships = $companyMemberships;

        return $this;
    }

    public function addCompanyMembership(CompanyMembership $companyMembership): self
    {
        $this->companyMemberships = [...($this->companyMemberships ?? []), $companyMembership];

        return $this;
    }

    /**
     * @param array<Invoice> $invoices
     */
    public function withInvoices(array $invoices): self
    {
        $this->invoices = $invoices;

        return $this;
    }

    public function addInvoice(Invoice $invoice): self
    {
        $this->invoices = [...($this->invoices ?? []), $invoice];

        return $this;
    }

    /**
     * @param array<Quote> $quotes
     */
    public function withQuotes(array $quotes): self
    {
        $this->quotes = $quotes;

        return $this;
    }

    public function addQuote(Quote $quote): self
    {
        $this->quotes = [...($this->quotes ?? []), $quote];

        return $this;
    }
}

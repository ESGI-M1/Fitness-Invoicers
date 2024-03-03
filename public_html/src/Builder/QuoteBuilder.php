<?php

namespace App\Builder;

use App\Entity\Company;
use App\Entity\Deposit;
use App\Entity\Invoice;
use App\Entity\Item;
use App\Enum\QuoteStatusEnum;
use App\Factory\QuoteFactory;

class QuoteBuilder implements BuilderInterface
{
    private ?float $discountAmount = null;
    private ?float $discountPercent = null;
    private ?QuoteStatusEnum $status = null;
    private ?Company $company = null;

    /**
     * @var array<Item>|null
     */
    private ?array $items = null;

    /**
     * @var array<Invoice>|null
     */
    private ?array $invoices = null;

    /**
     * @var array<Deposit>|null
     */
    private ?array $deposits = null;

    public function build(bool $persist = true): object
    {
        $quote = QuoteFactory::createOne(array_filter([
            'items' => $this->items,
            'discountAmount' => $this->discountAmount,
            'discountPercent' => $this->discountPercent,
            'status' => $this->status,
            'invoices' => $this->invoices,
            'deposits' => $this->deposits,
        ]));

        if ($persist) {
            $quote->save();
        }

        return $quote->object();
    }

    public function withDiscountAmount(float $discountAmount): self
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function withDiscountPercent(float $discountPercent): self
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    public function withStatus(QuoteStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @param array<Item> $items
     */
    public function withItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(Item $items): self
    {
        $this->items = [...($this->items ?? []), $items];

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

    public function addInvoice(Invoice $invoices): self
    {
        $this->invoices = [...($this->invoices ?? []), $invoices];

        return $this;
    }

    /**
     * @param array<Deposit> $deposits
     */
    public function withDeposits(array $deposits): self
    {
        $this->deposits = $deposits;

        return $this;
    }

    public function addDeposit(Deposit $deposits): self
    {
        $this->deposits = [...($this->deposits ?? []), $deposits];

        return $this;
    }
}

<?php

namespace App\Builder;

use App\Entity\Invoice;
use App\Entity\Quote;
use App\Factory\DepositFactory;

class DepositBuilder implements BuilderInterface
{
    private ?float $price = null;
    private ?Invoice $invoice = null;
    private ?Quote $quote = null;

    public function build(bool $persist = true): object
    {
        $deposit = DepositFactory::createOne(array_filter([
            'price' => $this->price,
            'invoice' => $this->invoice,
            'quote' => $this->quote,
        ]));

        if ($persist) {
            $deposit->save();
        }

        return $deposit->object();
    }

    public function withPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function onInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function onQuote(Quote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }
}

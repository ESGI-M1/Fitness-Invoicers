<?php

namespace App\Builder;

use App\Entity\Invoice;
use App\Entity\Product;
use App\Entity\Quote;
use App\Factory\ItemFactory;

class ItemBuilder implements BuilderInterface
{
    private ?int $quantity = null;
    private ?float $discountAmountOnItem = null;
    private ?float $discountAmountOnTotal = null;
    private ?float $taxes = null;
    private ?Quote $quote = null;
    private ?Product $product = null;
    private ?Invoice $invoice = null;

    public function build(bool $persist = true): object
    {
        $item = ItemFactory::createOne(array_filter([
            'quantity' => $this->quantity,
            'discountAmountOnItem' => $this->discountAmountOnItem,
            'discountAmountOnTotal' => $this->discountAmountOnTotal,
            'taxes' => $this->taxes,
            'quote' => $this->quote,
            'product' => $this->product,
            'invoice' => $this->invoice,
        ]));

        if ($persist) {
            $item->save();
        }

        return $item->object();
    }

    public function withQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function withDiscountAmountOnItem(float $discountAmountOnItem): self
    {
        $this->discountAmountOnItem = $discountAmountOnItem;

        return $this;
    }

    public function withDiscountAmountOnTotal(float $discountAmountOnTotal): self
    {
        $this->discountAmountOnTotal = $discountAmountOnTotal;

        return $this;
    }

    public function withDiscountPercentOnItem(float $discountPercentOnItem): self
    {
        $this->discountPercentOnItem = $discountPercentOnItem;

        return $this;
    }

    public function withDiscountPercentOnTotal(float $discountPercentOnTotal): self
    {
        $this->discountPercentOnTotal = $discountPercentOnTotal;

        return $this;
    }

    public function withTaxes(float $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function withProductLabel(string $productLabel): self
    {
        $this->productLabel = $productLabel;

        return $this;
    }

    public function withProductRef(string $productRef): self
    {
        $this->productRef = $productRef;

        return $this;
    }

    public function withProductPrice(float $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function onQuote(Quote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function withProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function onInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }
}

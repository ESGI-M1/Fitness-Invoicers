<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $discountAmountOnItem = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $discountAmountOnTotal = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $taxes = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private ?Quote $quote = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private ?Invoice $invoice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDiscountAmountOnItem(): ?float
    {
        return $this->discountAmountOnItem;
    }

    public function setDiscountAmountOnItem(?float $discountAmountOnItem): static
    {
        $this->discountAmountOnItem = $discountAmountOnItem;

        return $this;
    }

    public function getDiscountAmountOnTotal(): ?float
    {
        return $this->discountAmountOnTotal;
    }

    public function setDiscountAmountOnTotal(?float $discountAmountOnTotal): static
    {
        $this->discountAmountOnTotal = $discountAmountOnTotal;

        return $this;
    }

    public function getTaxes(): ?float
    {
        return $this->taxes;
    }

    public function setTaxes(?float $taxes): static
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }
}

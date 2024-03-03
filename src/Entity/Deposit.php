<?php

namespace App\Entity;

use App\Repository\DepositRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepositRepository::class)]
class Deposit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $amount = null;

    #[ORM\OneToOne(mappedBy: 'deposit', cascade: ['persist', 'remove'])]
    private ?Invoice $invoice = null;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        // unset the owning side of the relation if necessary
        if ($invoice === null && $this->invoice !== null) {
            $this->invoice->setDeposit(null);
        }

        // set the owning side of the relation if necessary
        if ($invoice !== null && $invoice->getDeposit() !== $this) {
            $invoice->setDeposit($this);
        }

        $this->invoice = $invoice;

        return $this;
    }

}

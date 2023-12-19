<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?invoice $invoice = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?paymentMethod $payment_method = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?paymentStatus $payment_status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getInvoice(): ?invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getPaymentMethod(): ?paymentMethod
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(?paymentMethod $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getPaymentStatus(): ?paymentStatus
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(?paymentStatus $payment_status): self
    {
        $this->payment_status = $payment_status;

        return $this;
    }
}

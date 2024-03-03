<?php

namespace App\Entity;

use App\Enum\PaymentMethodEnum;
use App\Enum\PaymentStatusEnum;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;


#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: PaymentStatusEnum::class)]
    private ?PaymentStatusEnum $status = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: PaymentMethodEnum::class)]
    private ?PaymentMethodEnum $method = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?Invoice $invoice = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

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

    public function getStatus(): ?PaymentStatusEnum
    {
        return $this->status;
    }

    public function setStatus(PaymentStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMethod(): ?PaymentMethodEnum
    {
        return $this->method;
    }

    public function setMethod(PaymentMethodEnum $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getInvoice(): ?invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }
}

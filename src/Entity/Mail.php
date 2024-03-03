<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $object = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'mails')]
    private ?customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'mails')]
    private ?invoice $invoice = null;

    #[ORM\ManyToOne(inversedBy: 'mails')]
    private ?quote $quote = null;

    private ?string $signature = null;
    #[ORM\Column(nullable: true)]
    private ?bool $joinPDF = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): static
    {
        $this->object = $object;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCustomer(): ?customer
    {
        return $this->customer;
    }

    public function setCustomer(?customer $customer): static
    {
        $this->customer = $customer;

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

    public function getQuote(): ?quote
    {
        return $this->quote;
    }

    public function setQuote(?quote $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function isJoinPDF(): ?bool
    {
        return $this->joinPDF;
    }

    public function setJoinPDF(?bool $joinPDF): static
    {
        $this->joinPDF = $joinPDF;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;

        return $this;
    }
}

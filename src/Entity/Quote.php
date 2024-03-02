<?php

namespace App\Entity;

use App\Enum\QuoteStatusEnum;
use App\Repository\QuoteRepository;
use App\Trait\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{

    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $discountAmount = null;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Item::class, orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: QuoteStatusEnum::class)]
    private ?QuoteStatusEnum $status = null;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Invoice::class)]
    private Collection $invoices;
    #[ORM\ManyToOne(inversedBy: 'quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expirationDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Mail::class)]
    private Collection $mails;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->mails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setQuote($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getQuote() === $this) {
                $item->setQuote(null);
            }
        }

        return $this;
    }

    public function getDiscountAmount(): ?float
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(?float $discountAmount): static
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function getStatus(): ?QuoteStatusEnum
    {
        return $this->status;
    }

    public function setStatus(QuoteStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setQuote($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getQuote() === $this) {
                $invoice->setQuote(null);
            }
        }

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getAmount(): float
    {
        $items = $this->getItems()->getValues();
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getTotalAmount();
        }

        return $amount;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getTotalAmount() : float
    {
        $items = $this->getItems();
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getProduct()->getPrice() * $item->getQuantity() * (1 - $item->getTaxes() / 100);
        }
        return $amount;
    }

    public function getTaxesAmount() : float
    {
        $items = $this->getItems();
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getProduct()->getPrice() * $item->getQuantity() * $item->getTaxes() / 100;
        }
        return $amount;
    }

    public function getTotalWithoutTaxes() : float
    {
        $items = $this->getItems();
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        return $amount;
    }


    public function isValid(): bool
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isValid()) {
                return false;
            }
        }

        return $this->getCustomer() !== null
            && $this->getCompany() !== null
            && $this->getDetails() !== null
            && $this->getExpirationDate() !== null
            && $this->getItems()->count() > 0
            && $this->getTotalAmount() > 0
            && $this->getTotalWithoutTaxes() > 0
            && $this->getTaxesAmount() >= 0
            && $this->getCustomer()->isValid()
            && $this->getCompany()->isValid();
    }

    public function getIsNotValidErrors(): array
    {

        $errors = [];
        if ($this->getCustomer() === null) {
            $errors[] = 'customer.not.valid';
        }

        if ($this->getCompany() === null) {
            $errors[] = 'company.not.valid';
        }

        if ($this->getDetails() === null) {
            $errors[] = 'details.are.required';
        }

        if ($this->getExpirationDate() === null) {
            $errors[] = 'expiration.date.is.required';
        }

        if ($this->getItems()->count() === 0) {
            $errors[] = 'items.are.required';
        }

        if ($this->getTotalAmount() <= 0) {
            $errors[] = 'items.total.amount.must.be.greater.than.0';
        }

        if ($this->getTotalWithoutTaxes() <= 0) {
            $errors[] = 'items.total.without.taxes.must.be.greater.than.0';
        }

        if ($this->getTaxesAmount() < 0) {
            $errors[] = 'items.taxes.amount.must.be.greater.or.equal.to.0';
        }

        if ($this->getCustomer() !== null) {
            $errors = array_merge($errors, $this->getCustomer()->getIsNotValidErrors());
        }

        if ($this->getCompany() !== null) {
            $errors = array_merge($errors, $this->getCompany()->getIsNotValidErrors());
        }

        foreach ($this->getItems() as $item) {
            if (!$item->isValid()) {
                $errors = array_merge($errors, $item->getIsNotValidErrors());
            }
        }

        return $errors;
    }

    public function isValidStepOne(): bool
    {
        return $this->getCustomer() !== null
            && $this->getCustomer()->isValid();
    }

    public function getIsNotValidStepOneErrors(): array
    {
        $errors = [];
        if ($this->getCustomer() === null) {
            $errors[] = 'customer.not.valid';
        }

        if ($this->getCustomer() !== null) {
            $errors = array_merge($errors, $this->getCustomer()->getIsNotValidErrors());
        }

        return $errors;
    }

    public function isValidStepTwo(): bool
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isValid()) {
                return false;
            }
        }

        return $this->getItems()->count() > 0
            && $this->getTotalAmount() > 0
            && $this->getTotalWithoutTaxes() > 0
            && $this->getTaxesAmount() >= 0;
    }

    public function getIsNotValidStepTwoErrors(): array
    {
        $errors = [];
        if ($this->getItems()->count() === 0) {
            $errors[] = 'items.are.required';
        }

        if ($this->getTotalAmount() <= 0) {
            $errors[] = 'items.total.amount.must.be.greater.than.0';
        }

        if ($this->getTotalWithoutTaxes() <= 0) {
            $errors[] = 'items.total.without.taxes.must.be.greater.than.0';
        }

        if ($this->getTaxesAmount() < 0) {
            $errors[] = 'items.taxes.amount.must.be.greater.or.equal.to.0';
        }

        foreach ($this->getItems() as $item) {
            if (!$item->isValid()) {
                $errors = array_merge($errors, $item->getIsNotValidErrors());
            }
        }

        return $errors;
    }

    public function isValidStepThree()
    {
        return $this->getDetails() !== null
            && $this->getExpirationDate() !== null
            && $this->getStatus() === QuoteStatusEnum::PENDING || $this->getStatus() === QuoteStatusEnum::SENT || $this->getStatus() === QuoteStatusEnum::ACCEPTED;
    }

    public function getIsNotValidStepThreeErrors(): array
    {
        $errors = [];
        if ($this->getDetails() === null) {
            $errors[] = 'details.are.required';
        }

        if ($this->getExpirationDate() === null) {
            $errors[] = 'expiration.date.is.required';
        }

        if ($this->getStatus() !== QuoteStatusEnum::PENDING && $this->getStatus() !== QuoteStatusEnum::SENT && $this->getStatus() !== QuoteStatusEnum::ACCEPTED) {
            $errors[] = 'status.is.not.valid';
        }

        return $errors;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeImmutable $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return Collection<int, Mail>
     */
    public function getMails(): Collection
    {
        return $this->mails;
    }

    public function addMail(Mail $mail): static
    {
        if (!$this->mails->contains($mail)) {
            $this->mails->add($mail);
            $mail->setQuote($this);
        }

        return $this;
    }

    public function removeMail(Mail $mail): static
    {
        if ($this->mails->removeElement($mail)) {
            // set the owning side to null (unless already changed)
            if ($mail->getQuote() === $this) {
                $mail->setQuote(null);
            }
        }

        return $this;
    }



}

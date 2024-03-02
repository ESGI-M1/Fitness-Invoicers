<?php

namespace App\Entity;

use App\Entity\Item;
use App\Entity\Quote;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Deposit;
use App\Enum\InvoiceStatusEnum;
use App\Repository\InvoiceRepository;
use App\Trait\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints\Date;


#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{

    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $discountAmount = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: InvoiceStatusEnum::class)]
    private ?InvoiceStatusEnum $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Quote $quote = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $customer = null;

    #[ORM\OneToMany(mappedBy: 'invoices', targetEntity: Item::class)]
    private Collection $items;

    #[Vich\UploadableField(mapping: 'invoicePdf', fileNameProperty: 'pdfName')]
    #[Assert\File(
        mimeTypes: ['application/pdf'],
        mimeTypesMessage: 'Le fichier doit être au format PDF')
    ]
    #[Ignore]
    private ?File $pdfFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $pdfName = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\OneToOne(inversedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?deposit $deposit = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalAmount = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Mail::class)]
    private Collection $mails;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Payment::class)]
    private Collection $payments;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->mails = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?InvoiceStatusEnum
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatusEnum $status): static
    {
        $this->status = $status;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
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

    public function getTaxesAmount() : float
    {
        $items = $this->getItems();
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getTaxesAmount();
        }
        return $amount;
    }

    public function getTotalWithoutTaxes() : float
    {
        $items = $this->getItems();
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getTotalWithoutTaxes();
        }
        return $amount;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setInvoices($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInvoices() === $this) {
                $item->setInvoices(null);
            }
        }

        return $this;
    }

    public function getPdfFile(): ?File
    {
        return $this->pdfFile;
    }

    public function setPdfFile(?File $pdfFile): static
    {
        $this->pdfFile = $pdfFile;

        if ($pdfFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getPdfName(): ?string
    {
        return $this->pdfName;
    }

    public function setPdfName(?string $pdfName): static
    {
        $this->pdfName = $pdfName;

        return $this;
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
            && $this->getDueDate() !== null
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

        if ($this->getDueDate() === null) {
            $errors[] = 'due.date.is.required';
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
                $errors[] = $item->getIsNotValidErrors();
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
            && $this->getDueDate() !== null
            && $this->getStatus() === InvoiceStatusEnum::VALIDATED || $this->getStatus() === InvoiceStatusEnum::SENT;
    }

    public function getIsNotValidStepThreeErrors(): array
    {
        $errors = [];
        if ($this->getDetails() === null) {
            $errors[] = 'details.are.required';
        }

        if ($this->getDueDate() === null) {
            $errors[] = 'due.date.is.required';
        }

        if ($this->getStatus() !== InvoiceStatusEnum::VALIDATED || $this->getStatus() !== InvoiceStatusEnum::SENT) {
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

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getDeposit(): ?deposit
    {
        return $this->deposit;
    }

    public function setDeposit(?deposit $deposit): static
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount +1;
    }

    public function setTotalAmount(?float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getOutStandingAmount(): float
    {
        if ($this->getDeposit() !== null) {
            return $this->getTotalAmount() - $this->getDeposit()->getAmount();
        }
        return $this->getTotalAmount();
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

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
            $mail->setInvoice($this);
        }

        return $this;
    }

    public function removeMail(Mail $mail): static
    {
        if ($this->mails->removeElement($mail)) {
            // set the owning side to null (unless already changed)
            if ($mail->getInvoice() === $this) {
                $mail->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setInvoice($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getInvoice() === $this) {
                $payment->setInvoice(null);
            }
        }

        return $this;
    }

}

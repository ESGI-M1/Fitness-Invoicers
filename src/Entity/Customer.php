<?php

namespace App\Entity;

use App\Enum\CustomerStatutEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\CustomerRepository;



#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: '`customer`')]
#[Vich\Uploadable]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $lastName;

    #[ORM\OneToOne(mappedBy: 'Customer', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Address $deliveryAddress = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Address $billingAddress = null;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Quote::class)]
    private Collection $quotes;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Mail::class)]
    private Collection $mails;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: CustomerStatutEnum::class)]
    private ?CustomerStatutEnum $status = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
        $this->quotes = new ArrayCollection();
        $this->mails = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setCustomer(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getCustomer() !== $this) {
            $user->setCustomer($this);
        }

        $this->user = $user;

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
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isValid() : bool
    {
        return $this->firstName !== ''
            && $this->lastName !== ''
            && $this->email !== ''
            && $this->deliveryAddress !== null
            && $this->billingAddress !== null
            && $this->deliveryAddress->isValid()
            && $this->billingAddress->isValid();
    }

    public function getIsNotValidErrors(): array
    {
        $errors = [];
        if ($this->firstName === '') {
            $errors[] = 'customer.firstName.required';
        }

        if ($this->lastName === '') {
            $errors[] = 'customer.lastName.required';
        }

        if ($this->email === '') {
            $errors[] = 'customer.email.required';
        }

        if($this->deliveryAddress === null) {
            $errors[] = 'customer.deliveryAddress.required';
        }

        if($this->billingAddress === null) {
            $errors[] = 'customer.billingAddress.required';
        }

        if (!$this->deliveryAddress->isValid()) {
            $errors = array_merge($errors, $this->deliveryAddress->getIsNotValidErrors());
        }

        if (!$this->billingAddress->isValid()) {
            $errors = array_merge($errors, $this->billingAddress->getIsNotValidErrors());
        }

        return $errors;
    }

    /**
     * @return Collection<int, Quote>
     */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(Quote $quote): static
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
            $quote->setCustomer($this);
        }

        return $this;
    }

    public function removeQuote(Quote $quote): static
    {
        if ($this->quotes->removeElement($quote)) {
            // set the owning side to null (unless already changed)
            if ($quote->getCustomer() === $this) {
                $quote->setCustomer(null);
            }
        }

        return $this;
    }

    public function getName() {
        return $this->lastName .  ' ' . $this->firstName;
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
            $mail->setCustomer($this);
        }

        return $this;
    }

    public function removeMail(Mail $mail): static
    {
        if ($this->mails->removeElement($mail)) {
            // set the owning side to null (unless already changed)
            if ($mail->getCustomer() === $this) {
                $mail->setCustomer(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?CustomerStatutEnum
    {
        return $this->status;
    }

    public function setStatus(?CustomerStatutEnum $status): static
    {
        $this->status = $status;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Enum\CompanyMembershipStatusEnum;
use App\Repository\CompanyRepository;
use App\Trait\TimestampableTrait;
use App\Trait\SluggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Ignore;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[Vich\Uploadable]
class Company implements \Serializable
{
    use TimestampableTrait;
    use SluggableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'company.name.not_blank')]
    #[Assert\Length(min: 3, minMessage: 'Le nom de la société doit être de minimum {{ limit }} caractères')]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 14)]
    #[Assert\NotBlank(message: 'Veillez renseigner le numéro de SIRET de votre société')]
    #[Assert\Regex(pattern: '/^[0-9]{14}$/', message: 'Le numéro de SIRET doit être composé de 14 chiffres')]
    private ?string $siret = null;

    #[Vich\UploadableField(mapping: 'companyLogo', fileNameProperty: 'imageName')]
    #[Assert\Image(
        maxSize: '250k',
        mimeTypes: ['image/jpeg', 'image/png'],
        maxHeight: 250,
        maxWidth: 250,
        minWidth: 48,
        minHeight: 48,
        maxSizeMessage: 'Le logo ne doit pas dépasser 250ko.',
        mimeTypesMessage: 'Le logo doit être au format JPG ou PNG.',
        maxHeightMessage: 'Le logo ne doit pas dépasser 250px de hauteur.',
        maxWidthMessage: 'Le logo ne doit pas dépasser 250px de largeur.',
        minWidthMessage: 'Le logo doit faire au moins 48px de largeur.',
        minHeightMessage: 'Le logo doit faire au moins 48px de hauteur.',
    )]
    #[Ignore]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'referentCompanies')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $referent = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Category::class, orphanRemoval: true)]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyMembership::class)]
    private Collection $companyMemberships;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Invoice::class, orphanRemoval: true)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Quote::class, orphanRemoval: true)]
    private Collection $quotes;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->companyMemberships = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->quotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getReferent(): ?User
    {
        return $this->referent;
    }

    public function setReferent(?User $referent): static
    {
        $this->referent = $referent;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setCompany($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCompany() === $this) {
                $category->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyMembership>
     */
    public function getCompanyMemberships(): Collection
    {
        return $this->companyMemberships;
    }

    public function addCompanyMembership(CompanyMembership $companyMembership): static
    {
        if (!$this->companyMemberships->contains($companyMembership)) {
            $this->companyMemberships->add($companyMembership);
            $companyMembership->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyMembership(CompanyMembership $companyMembership): static
    {
        if ($this->companyMemberships->removeElement($companyMembership)) {
            // set the owning side to null (unless already changed)
            if ($companyMembership->getCompany() === $this) {
                $companyMembership->setCompany(null);
            }
        }

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
            $invoice->setCompany($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCompany() === $this) {
                $invoice->setCompany(null);
            }
        }

        return $this;
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
            $quote->setCompany($this);
        }

        return $this;
    }

    public function removeQuote(Quote $quote): static
    {
        if ($this->quotes->removeElement($quote)) {
            // set the owning side to null (unless already changed)
            if ($quote->getCompany() === $this) {
                $quote->setCompany(null);
            }
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;

        if (null !== $image) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function userInCompany(User $user): bool
    {
        foreach ($this->companyMemberships as $companyMembership) {
            if ($companyMembership->getRelatedUser() === $user) {
                return true;
            }
        }

        return false;
    }

    public function userAcceptedInCompany(User $user): bool
    {
        foreach ($this->companyMemberships as $companyMembership) {
            if ($companyMembership->getRelatedUser() === $user && $companyMembership->getStatus() === CompanyMembershipStatusEnum::ACCEPTED) {
                return true;
            }
        }

        return false;
    }

    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->name,
            $this->siret,
            $this->imageName,
            $this->createdAt,
            $this->updatedAt,
        ]);
    }

    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->name,
            $this->siret,
            $this->imageName,
            $this->createdAt,
            $this->updatedAt,
        ] = unserialize($serialized);
    }

}

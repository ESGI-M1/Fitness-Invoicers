<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use App\Trait\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'company.name.not_blank')]
    #[Assert\Length(min: 3, minMessage: 'Le nom de la société doit être de minimum {{ limit }} caractères')]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name', 'id'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, length: 14)]
    #[Assert\NotBlank(message: 'Veillez renseigner le numéro de SIRET de votre société')]
    #[Assert\Regex(pattern: '/^[0-9]{14}$/', message: 'Le numéro de SIRET doit être composé de 14 chiffres')]
    private ?string $siret = null;

    #[ORM\ManyToOne(inversedBy: 'referentCompanies')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $referent = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Category::class, orphanRemoval: true)]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyMembership::class)]
    private Collection $companyMemberships;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->companyMemberships = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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
}

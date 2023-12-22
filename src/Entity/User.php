<?php

namespace App\Entity;

use App\Enum\CivilityEnum;
use App\Enum\CompanyMembershipStatusEnum;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'user.email.alreadyExist')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $lastName;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING , length: 255, enumType: CivilityEnum::class)]
    private ?CivilityEnum $civility = null;

    #[ORM\OneToMany(mappedBy: 'referent', targetEntity: Company::class)]
    private Collection $referentCompanies;

    #[ORM\OneToMany(mappedBy: 'relatedUser', targetEntity: CompanyMembership::class, orphanRemoval: true)]
    private Collection $companyMemberships;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->referentCompanies = new ArrayCollection();
        $this->companyMemberships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getCivility(): ?CivilityEnum
    {
        return $this->civility;
    }

    public function setCivility(CivilityEnum $civility): static
    {
        $this->civility = $civility;

        return $this;
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

    /**
     * @return Collection<int, Company>
     */
    public function getReferentCompanies(): Collection
    {
        return $this->referentCompanies;
    }

    public function addReferentCompany(Company $referentCompany): static
    {
        if (!$this->referentCompanies->contains($referentCompany)) {
            $this->referentCompanies->add($referentCompany);
            $referentCompany->setReferent($this);
        }

        return $this;
    }

    public function removeReferentCompany(Company $referentCompany): static
    {
        if ($this->referentCompanies->removeElement($referentCompany)) {
            // set the owning side to null (unless already changed)
            if ($referentCompany->getReferent() === $this) {
                $referentCompany->setReferent(null);
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
            $companyMembership->setRelatedUser($this);
        }

        return $this;
    }

    public function removeCompanyMembership(CompanyMembership $companyMembership): static
    {
        if ($this->companyMemberships->removeElement($companyMembership)) {
            // set the owning side to null (unless already changed)
            if ($companyMembership->getRelatedUser() === $this) {
                $companyMembership->setRelatedUser(null);
            }
        }

        return $this;
    }
}

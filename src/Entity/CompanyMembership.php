<?php

namespace App\Entity;

use App\Enum\CompanyMembershipStatusEnum;
use App\Repository\CompanyMembershipRepository;
use App\Trait\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyMembershipRepository::class)]
class CompanyMembership
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'companyMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'companyMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $relatedUser = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: CompanyMembershipStatusEnum::class)]
    private ?CompanyMembershipStatusEnum $status = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRelatedUser(): ?User
    {
        return $this->relatedUser;
    }

    public function setRelatedUser(?User $relatedUser): static
    {
        $this->relatedUser = $relatedUser;

        return $this;
    }

    public function getStatus(): ?CompanyMembershipStatusEnum
    {
        return $this->status;
    }

    public function setStatus(CompanyMembershipStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }
}

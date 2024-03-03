<?php

namespace App\Builder;

use App\Entity\Company;
use App\Enum\CivilityEnum;
use App\Factory\UserFactory;

class UserBuilder implements BuilderInterface
{
    private ?string $email = null;
    private ?string $password = null;
    private ?CivilityEnum $civility = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?bool $isVerified = null;

    /**
     * @var array<Company>|null
     */
    private ?array $referentCompanies = null;
    /**
     * @var array<CompanyMembershipBuilder>|null
     */
    private ?array $companyMemberships = null;

    public function build(bool $persist = true): object
    {
        $user = UserFactory::createOne(array_filter([
            'email' => $this->email,
            'password' => $this->password,
            'civility' => $this->civility,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'isVerified' => $this->isVerified,
            'referentCompanies' => $this->referentCompanies,
            'companyMemberships' => $this->companyMemberships,
        ]));

        if ($persist) {
            $user->save();
        }

        return $user->object();
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function withCivility(CivilityEnum $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function withFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function withLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function verified(): self
    {
        $this->isVerified = true;

        return $this;
    }

    public function notVerified(): self
    {
        $this->isVerified = false;

        return $this;
    }

    /**
     * @param array<Company> $referentCompanies
     */
    public function withReferentCompanies(array $referentCompanies): self
    {
        $this->referentCompanies = $referentCompanies;

        return $this;
    }

    /**
     * @param array<CompanyMembershipBuilder> $companyMemberships
     */
    public function withCompanyMemberships(array $companyMemberships): self
    {
        $this->companyMemberships = $companyMemberships;

        return $this;
    }
}

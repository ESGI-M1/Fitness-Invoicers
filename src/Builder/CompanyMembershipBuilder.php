<?php

namespace App\Builder;

use App\Entity\Company;
use App\Enum\CompanyMembershipStatusEnum;
use App\Factory\CompanyMembershipFactory;

class CompanyMembershipBuilder implements BuilderInterface
{
    private ?Company $company = null;
    private ?CompanyMembershipStatusEnum $status = null;
    private ?object $relatedUser = null;

    public function build(bool $persist = true): object
    {
        $companyMembership = CompanyMembershipFactory::createOne(array_filter([
            'company' => $this->company,
            'status' => $this->status,
            'relatedUser' => $this->relatedUser,
        ]));

        if ($persist) {
            $companyMembership->save();
        }

        return $companyMembership->object();
    }

    public function withCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function withStatus(CompanyMembershipStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withRelatedUser(object $relatedUser): self
    {
        $this->relatedUser = $relatedUser;

        return $this;
    }
}
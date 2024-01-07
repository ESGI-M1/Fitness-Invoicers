<?php

namespace App\Builder;

final class ThereIs
{
    public static function aCompany(): CompanyBuilder
    {
        return new CompanyBuilder();
    }

    public static function aUser(): UserBuilder
    {
        return new UserBuilder();
    }

    public static function aProduct(): ProductBuilder
    {
        return new ProductBuilder();
    }

    public static function aCategory(): CategoryBuilder
    {
        return new CategoryBuilder();
    }

    public static function aCompanyMembership(): CompanyMembershipBuilder
    {
        return new CompanyMembershipBuilder();
    }
}

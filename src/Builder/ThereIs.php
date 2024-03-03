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

    public static function aDeposit(): DepositBuilder
    {
        return new DepositBuilder();
    }

    public static function anInvoice(): InvoiceBuilder
    {
        return new InvoiceBuilder();
    }

    public static function anItem(): ItemBuilder
    {
        return new ItemBuilder();
    }

    public static function aQuote(): QuoteBuilder
    {
        return new QuoteBuilder();
    }

    public static function anAddress(): AddressBuilder
    {
        return new AddressBuilder();
    }
}

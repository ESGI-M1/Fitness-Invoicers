<?php

namespace App\Factory;

use App\Entity\CompanyMembership;
use App\Enum\CompanyMembershipStatusEnum;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<CompanyMembership>
 */
final class CompanyMembershipFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'status' => self::faker()->randomElement(CompanyMembershipStatusEnum::class),
        ];
    }

    protected static function getClass(): string
    {
        return CompanyMembership::class;
    }
}

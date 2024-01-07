<?php

namespace App\Factory;

use App\Entity\Company;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Company>
 */
final class CompanyFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'siret' => self::faker()->numerify('##############'),
            'categories' => [],
            'companyMemberships' => [],
        ];
    }

    protected static function getClass(): string
    {
        return Company::class;
    }
}

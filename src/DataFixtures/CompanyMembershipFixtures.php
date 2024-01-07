<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use App\Entity\Company;
use App\Factory\CompanyFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Proxy;

class CompanyMembershipFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Proxy<Company> $company */
        foreach (CompanyFactory::all() as $company) {
            if (null !== $company->getReferent()) {
                $companyMembership = ThereIs::aCompanyMembership()
                    ->withCompany($company->object())
                    ->withRelatedUser($company->getReferent())
                    ->build();

                $manager->persist($companyMembership);
            }

            for ($i = 0; $i < random_int(1, 5); ++$i) {
                $companyMembership = ThereIs::aCompanyMembership()
                    ->withCompany($company->object())
                    ->withRelatedUser(UserFactory::new())
                    ->build();

                $manager->persist($companyMembership);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}

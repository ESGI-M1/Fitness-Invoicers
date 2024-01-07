<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Factory;

class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $referent = UserFactory::random()->object();

            $company = ThereIs::aCompany();

            if (Factory::faker()->boolean(80)) {
                $company->withReferent($referent);
            }

            $company = $company->build();

            $manager->persist($company);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}

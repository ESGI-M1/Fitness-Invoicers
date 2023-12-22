<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use App\Factory\CompanyFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        foreach (CompanyFactory::all() as $company) {
            if (null === $company->getReferent()) {
                continue;
            }

            for ($i = 0; $i < random_int(1, 5); $i++) {
                $category = ThereIs::aCategory()
                    ->withCompany($company->object())
                    ->build();

                $manager->persist($category);
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
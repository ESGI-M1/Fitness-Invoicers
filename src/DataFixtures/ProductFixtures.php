<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use App\Factory\CompanyFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Factory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (CompanyFactory::all() as $company) {
            if (null === $company->getReferent()) {
                continue;
            }

            for ($i = 0; $i < random_int(1, 10); ++$i) {
                $product = ThereIs::aProduct();

                foreach ($company->getCategories() as $category) {
                    if (Factory::faker()->boolean(20)) {
                        $product->inCategory($category);
                    }
                }

                $product = $product->build();

                $manager->persist($product);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
            CategoryFixtures::class,
        ];
    }
}

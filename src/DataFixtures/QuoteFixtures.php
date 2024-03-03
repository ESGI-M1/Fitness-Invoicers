<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuoteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 2; ++$i) {
            $quote = ThereIs::aQuote()->build();

            $manager->persist($quote);
        }

        $manager->flush();
    }
}

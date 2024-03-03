<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InvoiceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 2; ++$i) {
            $invoice = ThereIs::anInvoice()->build();

            $manager->persist($invoice);
        }

        $manager->flush();
    }
}

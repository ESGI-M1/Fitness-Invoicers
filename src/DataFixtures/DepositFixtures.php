<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use App\Entity\Invoice;
use App\Entity\Quote;
use App\Factory\InvoiceFactory;
use App\Factory\QuoteFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Proxy;

class DepositFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * @var Proxy<Quote> $quote
         */
        foreach (QuoteFactory::all() as $quote) {
            if (random_int(0, 1)) {
                continue;
            }

            $deposit = ThereIs::aDeposit()->onQuote($quote->object())->build();

            $manager->persist($deposit);
        }

        /**
         * @var Proxy<Invoice> $invoice
         */
        foreach (InvoiceFactory::all() as $invoice) {
            if (random_int(0, 1)) {
                continue;
            }

            $deposit = ThereIs::aDeposit()->onInvoice($invoice->object())->build();

            $manager->persist($deposit);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            QuoteFixtures::class,
            InvoiceFixtures::class,
        ];
    }
}

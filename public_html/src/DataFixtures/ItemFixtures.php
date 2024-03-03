<?php

namespace App\DataFixtures;

use App\Builder\ThereIs;
use App\Entity\Product;
use App\Factory\ProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Proxy;

class ItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Proxy<Product> $product */
        foreach (ProductFactory::all() as $product) {
            if (random_int(0, 1)) {
                continue;
            }

            $item = ThereIs::anItem()->withProduct($product->object());

            switch (random_int(0, 2)) {
                case 1:
                    $item->onQuote(ThereIs::aQuote()->build());
                    break;
                case 2:
                    $item->onInvoice(ThereIs::anInvoice()->build());
                    break;
                default:
                    $quote = ThereIs::aQuote()->build();
                    $item->onQuote($quote);
                    $item->onInvoice(ThereIs::anInvoice()->onQuote($quote)->build());
                    break;
            }

            $manager->persist($item->build());
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
}

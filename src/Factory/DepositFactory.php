<?php

namespace App\Factory;

use App\Entity\Deposit;
use Zenstruck\Foundry\ModelFactory;

/**
 * @extends ModelFactory<Deposit>
 */
final class DepositFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'price' => self::faker()->randomFloat(),
        ];
    }

    protected static function getClass(): string
    {
        return Deposit::class;
    }
}

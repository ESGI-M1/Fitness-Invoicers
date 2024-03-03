<?php

namespace App\Builder;

/**
 * @template T of object
 */
interface BuilderInterface
{
    /**
     * @return T
     */
    public function build(bool $persist = true): object;
}

<?php

namespace App\Services\Integration\Transaction\Adapter\Bigtex;

use App\Services\Integration\Transaction\Adapter\Pj\Inventory as PjInventory;

/**
 * Class Inventory
 * @package App\Services\Integration\Transaction\Adapter\Bigtex
 */
class Inventory extends PjInventory
{
    private const MANUFACTURE_NAME = 'Big Tex Trailers';

    /**
     * @return string
     */
    protected function getManufactureName(): string
    {
        return self::MANUFACTURE_NAME;
    }
}

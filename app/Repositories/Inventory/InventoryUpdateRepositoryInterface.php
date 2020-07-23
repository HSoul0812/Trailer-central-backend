<?php


namespace App\Repositories\Inventory;

/**
 * Interface InventoryUpdateRepositoryInterface
 * @package App\Repositories\Inventory
 */
interface InventoryUpdateRepositoryInterface
{
    public function insertOrUpdate($params);
}

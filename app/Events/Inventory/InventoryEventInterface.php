<?php


namespace App\Events\Inventory;


interface InventoryEventInterface
{
    /**
     * @return string
     */
    public function getAction();
}

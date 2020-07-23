<?php

namespace App\Events\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class InventoryDeleted
 * @package App\Events\Inventory
 */
class InventoryDeletedEvent implements InventoryEventInterface
{
    const ACTION = 'delete';

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Inventory
     */
    public $inventory;

    /**
     * InventoryDeleted constructor.
     * @param Inventory $inventory
     */
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return self::ACTION;
    }
}

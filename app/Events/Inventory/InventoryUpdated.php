<?php


namespace App\Events\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class InventoryUpdated
 * @package App\Events\Inventory
 */
class InventoryUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Inventory
     */
    public $inventory;

    /**
     * Free-form array add any details you need
     * @var array
     */
    public $details;

    /**
     * @param Inventory $inventory
     * @param array $details
     */
    public function __construct(Inventory $inventory, array $details = [])
    {
        $this->inventory = $inventory;
        $this->details = $details;
    }

}

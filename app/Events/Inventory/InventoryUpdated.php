<?php


namespace App\Events\Inventory;

use App\Models\Inventory\Inventory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inventory;

    /**
     * Free-form array add any details you need
     * @var array
     */
    public $details;

    public function __construct(Inventory $inventory, $details = [])
    {
        $this->inventory = $inventory;
        $this->details = $details;
    }

}

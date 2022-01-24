<?php
namespace App\Events\Ecommerce;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QtyUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $part_id;
    private $quantity;

    /**
     * QtyUpdated constructor.
     * @param $part_id
     */
    public function __construct($part_id, $qty)
    {
        $this->part_id = $part_id;
        $this->quantity = $qty;
    }

    /**
     * @return mixed
     */
    public function getPartId()
    {
        return $this->part_id;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
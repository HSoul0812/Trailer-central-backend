<?php


namespace App\Events\Parts;


use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PartQtyUpdated
 *
 * Whenever a part qty is updated in a service
 *
 * @package App\Events\Parts
 */
class PartQtyUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $part;
    /**
     * @var BinQuantity
     */
    public $binQuantity;
    /**
     * Free-form array add any details you need
     * @var array
     */
    public $details;

    public function __construct(Part $part, ?BinQuantity $binQuantity = null, $details = [])
    {
        $this->part = $part;
        $this->binQuantity = $binQuantity;
        $this->details = $details;
    }

}

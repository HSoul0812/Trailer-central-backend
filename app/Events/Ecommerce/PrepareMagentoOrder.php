<?php

declare(strict_types=1);

namespace App\Events\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\WithGetter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @property-read CompletedOrder $order
 */
class PrepareMagentoOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels, WithGetter;

    /** @var CompletedOrder */
    private $order;

    public function __construct(CompletedOrder $order)
    {
        $this->order = $order;
    }
}

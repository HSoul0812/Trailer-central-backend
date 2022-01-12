<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce\Refund;

use App\Http\Requests\Request;
use App\Models\Parts\Textrail\Part;
use App\Repositories\Ecommerce\CompletedOrderRepository;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Parts\Textrail\PartRepository;

/**
 * @property int $textrail_order_id
 * @property array{sku: string, qty: int} $Items
 * @property array{id: int, qty: int} $parts
 */
class CreateReturnRequest extends Request
{
    /**
     * @var PartRepository
     */
    private $partRepository;

    /**
     * @var CompletedOrderRepository
     */
    private $orderRepository;

    public function getRules(): array
    {
        return [
            'textrail_order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,ecommerce_order_id',
            'Rma' => 'integer|min:1|required',
            'Items' => 'array|required',
            'Items.*.Sku' => 'required|exists:textrail_parts,sku',
            'Items.*.Qty' => 'required|int:min:1',
        ];
    }

    /**
     * @return array indexed array by part id
     */
    public function parts(): array
    {
        $indexedParts = [];

        foreach ($this->input('Items', []) as $item) {
            /** @var Part $part */
            $part = $this->getPartRepository()->getBySku($item['Sku']);

            $indexedParts[$part->id] = ['id' => $part->id, 'qty' => $item['Qty']];
        }

        return $indexedParts;
    }

    public function orderId(): int
    {
        return $this->getOrderRepository()->get(['ecommerce_order_id' => $this->textrail_order_id])->id;
    }

    public function rma(): int
    {
        return (int)$this->input('Rma');
    }

    protected function getPartRepository(): PartRepository
    {
        return $this->partRepository ?? app(PartRepository::class);
    }

    protected function getOrderRepository(): CompletedOrderRepository
    {
        return $this->orderRepository ?? app(CompletedOrderRepositoryInterface::class);
    }
}

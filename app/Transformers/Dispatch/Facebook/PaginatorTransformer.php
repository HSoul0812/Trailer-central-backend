<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\MarketplaceInventory;
use App\Transformers\Dispatch\Facebook\InventoryTransformer;
use League\Fractal\TransformerAbstract;

class PaginatorTransformer extends TransformerAbstract
{
    /**
     * @var InventoryTransformer
     */
    private $inventoryTransformer;


    protected $defaultIncludes = [
        'inventory'
    ];

    public function __construct(InventoryTransformer $inventoryTransformer) {
        $this->inventoryTransformer = $inventoryTransformer;
    }

    public function transform(MarketplaceInventory $inventory)
    {
        return [
            'type' => $inventory->type,
            'page' => $inventory->paginator ? $inventory->paginator->getCurrentPage() : 0,
            'pages' => $inventory->paginator ? $inventory->paginator->getLastPage() : 0,
            'count' => $inventory->paginator ? $inventory->paginator->getCount() : 0,
            'total' => $inventory->paginator ? $inventory->paginator->getTotal() : 0,
            'per_page' => $inventory->paginator ? $inventory->paginator->getPerPage() : 0
        ];
    }

    public function includeInventory(MarketplaceInventory $inventory)
    {
        if($inventory->inventory) {
            return $this->collection($inventory->inventory, $this->inventoryTransformer);
        }
        return $this->null;
    }
}
<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceInventory;
use App\Transformers\Dispatch\Facebook\InventoryTransformer;
use App\Transformers\Dispatch\TunnelTransformer;
use League\Fractal\TransformerAbstract;

class DealerTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'tunnels',
        'inventory'
    ];

    public function __construct(
        TunnelTransformer $tunnelTransformer,
        InventoryTransformer $inventoryTransformer
    ) {
        $this->tunnelTransformer = $tunnelTransformer;
        $this->inventoryTransformer = $inventoryTransformer;
    }

    public function transform(DealerFacebook $dealer)
    {
        return [
            'id' => $dealer->dealerId,
            'name' => $dealer->dealerName,
            'integration' => $dealer->integrationId,
            'fb' => [
                'username' => $dealer->fbUsername,
                'password' => $dealer->fbPassword
            ],
            'auth' => [
                'type' => $dealer->authType,
                'username' => $dealer->authUsername,
                'password' => $dealer->authPassword
            ]
        ];
    }

    public function includeTunnels(DealerFacebook $dealer)
    {
        return $this->collection($dealer->tunnels, $this->tunnelTransformer);
    }

    public function includeInventory(DealerFacebook $dealer)
    {
        return $this->item($dealer->inventory, function(MarketplaceInventory $inventory) {
            return [
                'type' => $inventory->type,
                $inventory->type => $inventory->inventory ? $this->collectInventory($inventory->inventory) : null,
                'page' => $inventory->paginator ? $inventory->paginator->getCurrentPage() : 0,
                'pages' => $inventory->paginator ? $inventory->paginator->getLastPage() : 0,
                'count' => $inventory->paginator ? $inventory->paginator->getCount() : 0,
                'total' => $inventory->paginator ? $inventory->paginator->getTotal() : 0,
                'per_page' => $inventory->paginator ? $inventory->paginator->getPerPage() : 0
            ];
        });
    }


    /**
     * Return Collection of Inventory
     * 
     * Collection<InventoryFacebook>
     * @return array
     */
    public function collectInventory(Collection $listings) {
        return $this->collection($listings, $this->inventoryTransformer);
    }
}
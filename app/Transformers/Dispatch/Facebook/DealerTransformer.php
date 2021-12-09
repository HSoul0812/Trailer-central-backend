<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Transformers\Dispatch\Facebook\InventoryTransformer;
use App\Transformers\Dispatch\TunnelTransformer;
use League\Fractal\TransformerAbstract;

class DealerTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'tunnels',
        'inventoryMissing',
        'inventoryUpdates',
        'inventorySold'
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

    public function includeInventoryMissing(DealerFacebook $dealer)
    {
        if($dealer->inventory && $dealer->inventory->missing) {
            return $this->collection($dealer->inventory->missing, $this->inventoryTransformer);
        }
        return $this->null();
    }

    public function includeInventoryUpdates(DealerFacebook $dealer)
    {
        if($dealer->inventory && $dealer->inventory->updates) {
            return $this->collection($dealer->inventory->updates, $this->inventoryTransformer);
        }
        return $this->null();
    }

    public function includeInventorySold(DealerFacebook $dealer)
    {
        if($dealer->inventory && $dealer->inventory->sold) {
            return $this->collection($dealer->inventory->sold, $this->inventoryTransformer);
        }
        return $this->null();
    }
}
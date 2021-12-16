<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceInventory;
use App\Transformers\Dispatch\Facebook\InventoryTransformer;
use App\Transformers\Dispatch\TunnelTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as Pagination;

class DealerTransformer extends TransformerAbstract
{
    /**
     * @var TunnelTransformer
     */
    private $tunnelTransformer;

    /**
     * @var InventoryTransformer
     */
    private $inventoryTransformer;

    /**
     * @var Manager
     */
    private $fractal;


    protected $defaultIncludes = [
        'tunnels',
        'inventory'
    ];

    public function __construct(
        TunnelTransformer $tunnelTransformer,
        InventoryTransformer $inventoryTransformer,
        Manager $fractal
    ) {
        $this->tunnelTransformer = $tunnelTransformer;
        $this->inventoryTransformer = $inventoryTransformer;

        // Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());
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

    /*public function includeInventory(DealerFacebook $dealer)
    {
        $collection = new Pagination($dealer->inventory->inventory, $this->inventoryTransformer);
        $collection->setPaginator($dealer->inventory->paginator);
        return $collection;
    }*/

    public function includeInventory(DealerFacebook $dealer)
    {
        return $this->item($dealer->inventory, function(MarketplaceInventory $inventory) {
            // Return Response
            if(empty($inventory->inventory)) {
                $data = new Pagination($inventory->inventory, $this->inventoryTransformer);
                $inventory = $this->fractal->createData($data)->toArray();
            }

            // Return Formatted Array
            return [
                'type' => $inventory->type,
                $inventory->type => $inventory->inventory ? $inventory : null,
                'page' => $inventory->paginator ? $inventory->paginator->getCurrentPage() : 0,
                'pages' => $inventory->paginator ? $inventory->paginator->getLastPage() : 0,
                'count' => $inventory->paginator ? $inventory->paginator->getCount() : 0,
                'total' => $inventory->paginator ? $inventory->paginator->getTotal() : 0,
                'per_page' => $inventory->paginator ? $inventory->paginator->getPerPage() : 0
            ];
        });
    }
}
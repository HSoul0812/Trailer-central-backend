<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\Cache\InvalidateByDealerRequest;
use App\Services\Inventory\InventoryServiceInterface;
use Dingo\Api\Http\Request;

class InventoryCacheController extends RestfulControllerV2
{
    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    public function __construct(InventoryServiceInterface $inventoryService)
    {
        $this->middleware('inventory.cache.permission');

        $this->inventoryService = $inventoryService;
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|void
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function invalidateByDealer(Request $request)
    {
        $request = new InvalidateByDealerRequest($request->all());

        if ($request->validate()) {
            $this->inventoryService->invalidateCacheReindexAndGenerateImageOverlaysByDealerIds($request->dealerIds());

            return $this->acceptedResponse();
        }

        return $this->response->errorBadRequest();
    }
}

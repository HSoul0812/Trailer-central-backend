<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\SaveInventoryAttributeRequest;
use App\Services\Inventory\InventoryAttributeServiceInterface;
use App\Transformers\Inventory\SaveInventoryAttributeTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class InventoryAttributeController
 *
 * @package App\Http\Controllers\v1\Inventory
 */
class InventoryAttributeController extends RestfulControllerV2
{
    /**
     * @var InventoryAttributeServiceInterface $service
     */
    private $service;

    /**
     * @param InventoryAttributeServiceInterface $service
     */
    public function __construct(InventoryAttributeServiceInterface $service)
    {
        $this->middleware('setDealerIdOnRequest')->only(['update']);
        $this->service = $service;
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update(int $id, Request $request): Response
    {
        $inventoryAttributeRequest = new SaveInventoryAttributeRequest(
            ['inventory_id' => $id] + $request->all()
        );

        if ($inventoryAttributeRequest->validate()) {
            $transformedData = resolve(SaveInventoryAttributeTransformer::class)->transform(
                $inventoryAttributeRequest->all()
            );

            $inventory = $this->service->update($transformedData);

            return $this->updatedResponse($inventory->getKey());
        }

        return $this->response->errorBadRequest();
    }
}

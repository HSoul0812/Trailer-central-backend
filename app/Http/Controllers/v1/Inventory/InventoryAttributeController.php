<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\SaveInventoryAttributeRequest;
use App\Services\Inventory\InventoryAttributeServiceInterface;
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
        $this->service = $service;
    }

    public function update(int $id, Request $request): Response
    {
        $inventoryAttributeRequest = new SaveInventoryAttributeRequest(
            ['inventoryId' => $id] + $request->all()
        );

        if ($inventoryAttributeRequest->validate()) {
            $transformer = app()->make(SaveInventoryAttributeRequest::class);
            $inventoryAttributeRequest->setTransformer($transformer);

            $inventory = $this->service->update($inventoryAttributeRequest->all());

            return $this->updatedResponse(44);
        }

        return $this->response->errorBadRequest();
    }
}

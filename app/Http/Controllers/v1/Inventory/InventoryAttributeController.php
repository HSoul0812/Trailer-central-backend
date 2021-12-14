<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\SaveInventoryAttributeRequest;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Transformers\Inventory\AttributeTransformer;
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
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * AttributesController constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function update(int $id, Request $request): Response
    {
        // SaveInventoryAttributeRequest $request,
        // dd($inventory);
        $inventoryAttributeRequest = new SaveInventoryAttributeRequest(
            ['inventoryId' => $id] + $request->all()
        );

        $transformer = app()->make(SaveInventoryAttributeRequest::class);
        $inventoryAttributeRequest->setTransformer($transformer);

        if (!$inventoryAttributeRequest->validate()) {
            // || !($inventory = $this->inventoryService->update($inventoryAttributeRequest->all()))
            return $this->response->errorBadRequest();
        }

        return $this->updatedResponse(44);
    }
}

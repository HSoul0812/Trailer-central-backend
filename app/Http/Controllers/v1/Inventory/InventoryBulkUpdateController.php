<?php

namespace App\Http\Controllers\v1\Inventory;

use Exception;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Inventory\InventoryBulkUpdateManufacturerRequest;
use App\Services\Inventory\InventoryBulkUpdateManufacturerServiceInterface;

/**
 * Class InventoryBulkUpdateController
 *
 * @package App\Http\Controllers\v1\Inventory
 */
class InventoryBulkUpdateController extends RestfulController
{

    /**
     * @var InventoryBulkUpdateManufacturerServiceInterface
     */
    protected $inventoryBulkUpdateManufacturerService;

    /**
     * Create a new controller instance.
     *
     * @param InventoryBulkUpdateManufacturerServiceInterface $inventoryBulkUpdateManufacturerService
     */
    public function __construct(
        InventoryBulkUpdateManufacturerServiceInterface $inventoryBulkUpdateManufacturerService
    ) {
        $this->inventoryBulkUpdateManufacturerService = $inventoryBulkUpdateManufacturerService;
    }

    /**
     * Updates Inventory Manufacturers
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function bulkUpdateManufacturer(Request $request): Response
    {
        $request = new InventoryBulkUpdateManufacturerRequest($request->all());

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $this->inventoryBulkUpdateManufacturerService->bulkUpdateManufacturer($request->all());

        return $this->response->array([
            'status' => 'success',
            'message' => 'Updating Inventories'
        ]);
    }
}

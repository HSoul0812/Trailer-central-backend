<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Repositories\Inventory\InventoryBulkUpdateRepository;
use Dingo\Api\Http\Response;
use Exception;
use Dingo\Api\Http\Request;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Showroom\ShowroomGetRequest;
use App\Transformers\Inventory\ManufacturerTransformer;
use App\Repositories\Showroom\ShowroomBulkUpdateRepository;
use App\Http\Requests\Showroom\ShowroomBulkUpdateYearRequest;
use App\Http\Requests\Showroom\ShowroomBulkUpdateVisibilityRequest;

/**
 * Class InventoryBulkUpdateController
 *
 * @package App\Http\Controllers\v1\Inventory
 */
class InventoryBulkUpdateController extends RestfulController
{
    /**
     * @var InventoryBulkUpdateRepository
     */
    protected $inventoryBulkUpdateRepository;


    /**
     * Create a new controller instance.
     *
     * @param InventoryBulkUpdateRepository $inventoryBulkUpdateRepository
     */
    public function __construct(
        InventoryBulkUpdateRepository $inventoryBulkUpdateRepository
    ) {
        $this->inventoryBulkUpdateRepository = $inventoryBulkUpdateRepository;
    }

    /**
     * Updates Inventory Manufacturers
     *
     * @param Request $request
     * @return Response
     */
    public function bulkUpdateManufacturer(Request $request): Response
    {
        $request = new InventoryBulkUpdateManufacturerRequest($request->all());

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        try {
            $this->inventoryBulkUpdateRepository->bulkUpdateManufacturer($request->all());

            return $this->response->array([
                'status' => 'success',
                'message' => 'Updating Inventories'
            ]);
        } catch (Exception $e) {
            return $this->response->array([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}

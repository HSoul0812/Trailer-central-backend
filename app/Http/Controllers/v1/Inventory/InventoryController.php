<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Http\Controllers\RestfulController;
use App\Http\Requests\Inventory\CreateInventoryRequest;
use App\Http\Requests\Inventory\DeleteInventoryRequest;
use App\Http\Requests\Inventory\GetInventoryHistoryRequest;
use App\Repositories\Inventory\InventoryHistoryRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\SaveInventoryTransformer;
use App\Transformers\Inventory\InventoryHistoryTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request;
use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Transformers\Inventory\InventoryTransformer;
use Dingo\Api\Http\Response;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class InventoryController
 * @package App\Http\Controllers\v1\Inventory
 */
class InventoryController extends RestfulController
{
    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var InventoryHistoryRepositoryInterface
     */
    protected $inventoryHistoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param  InventoryServiceInterface  $inventoryService
     * @param  InventoryRepositoryInterface  $inventoryRepository
     * @param  InventoryHistoryRepositoryInterface  $inventoryHistoryRepository
     */
    public function __construct(
        InventoryServiceInterface $inventoryService,
        InventoryRepositoryInterface $inventoryRepository,
        InventoryHistoryRepositoryInterface $inventoryHistoryRepository
    )
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'destroy']);
        $this->middleware('inventory.create.permission')->only(['create']);

        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
        $this->inventoryHistoryRepository = $inventoryHistoryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory",
     *     description="Retrieve a list of inventory",

     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Inventory price can be in format: [10 TO 100], [10], [10.0 TO 100.0]",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetInventoryRequest($request->all());

        if ( $request->validate() ) {
            if ($request->has('only_floorplanned') && !empty($request->input('only_floorplanned'))) {
                /**
                 * Filter only floored inventories to pay
                 * https://crm.trailercentral.com/accounting/floorplan-payment
                 */
                return $this->response->paginator($this->inventoryRepository->getFloorplannedInventory($request->all()), new InventoryTransformer());
            } else {
                return $this->response->paginator($this->inventoryRepository->getAll($request->all(), true, true), new InventoryTransformer());
            }
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/{id}",
     *     description="Retrieve a item",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a item",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $id
     * @return \Dingo\Api\Http\Response
     */
    public function show(int $id) {
        return $this->response->item($this->inventoryRepository->get(['id' => $id]), new InventoryTransformer());
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws BindingResolutionException
     */
    public function create(Request $request): Response
    {
        $inventoryRequest = new CreateInventoryRequest($request->all());

        $transformer = app()->make(SaveInventoryTransformer::class);
        $inventoryRequest->setTransformer($transformer);

        if (!$inventoryRequest->validate() || !($inventory = $this->inventoryService->create($inventoryRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->createdResponse($inventory->inventory_id);
    }

    /**
     * @OA\Delete(
     *     path="/api/inventory/{id}",
     *     description="Delete a item",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms part was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $id
     * @return \Dingo\Api\Http\Response|void
     */
    public function destroy(int $id)
    {
        $request = new DeleteInventoryRequest(['id' => $id]);

        if ($request->validate() && $this->inventoryService->delete($id)) {
            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/{inventory_id}/history",
     *     description="Retrieve a list of transactions belong to a inventory unit",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: in:created_at,-created_at,type,-type,subtype,-subtype,customer_name,-customer_name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search String",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="inventory_id",
     *         in="query",
     *         description="Inventory identifier",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         description="Customer identifier",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of transactions belong to a inventory unit",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param  int $inventoryId
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged-in dealer,
     *                       or there were some bad request
     * @throws Exception when there were db errors
     */
    public function history(int $inventoryId, Request $request): Response
    {
        $request = new GetInventoryHistoryRequest(
            array_merge(['inventory_id' => $inventoryId], $request->all())
        );

        if ($request->validate()) {
            return $this->response->paginator(
                $this->inventoryHistoryRepository->getAll($request->all(), true),
                new InventoryHistoryTransformer()
            );
        }

        $this->response->errorBadRequest();
    }
}

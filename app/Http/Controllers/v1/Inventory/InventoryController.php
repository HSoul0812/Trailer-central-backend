<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Inventory\CreateInventoryRequest;
use App\Http\Requests\Inventory\DeleteInventoryRequest;
use App\Services\Inventory\InventoryService;
use Dingo\Api\Http\Request;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Transformers\Inventory\InventoryTransformer;

class InventoryController extends RestfulController
{
    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var InventoryService
     */
    protected $inventoryService;

    /**
     * Create a new controller instance.
     *
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param InventoryService $inventoryService
     */
    public function __construct(InventoryRepositoryInterface $inventoryRepository, InventoryService $inventoryService)
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->inventoryService = $inventoryService;
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
            return $this->response->paginator($this->inventoryRepository->getAll($request->all(), true, true), new InventoryTransformer());
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
     */
    public function create(Request $request)
    {
        $request = new CreateInventoryRequest($request->all());

        if ($request->validate() && $this->inventoryService->create($request->all())) {
            return true;
        }

        return $this->response->errorBadRequest();
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

        if ($request->validate() && $this->inventoryRepository->delete(['id' => $id])) {
            return $this->response->noContent();
        }

        return $this->response->errorBadRequest();
    }
}

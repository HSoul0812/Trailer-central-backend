<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Inventory\DeleteInventoryRequest;
use App\Services\Inventory\InventoryService;
use Dingo\Api\Http\Request;
use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Transformers\Inventory\InventoryTransformer;

class InventoryController extends RestfulController
{
    /**
     * @var InventoryService
     */
    protected $inventoryService;

    /**
     * Create a new controller instance.
     *
     * @param InventoryService $inventoryService
     */
    public function __construct(InventoryService $inventoryService)
    {
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
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response|void
     */
    public function index(Request $request) {
        $request = new GetInventoryRequest($request->all());

        if ( $request->validate() ) {
            return $this->response->paginator($this->inventoryService->getAll($request->all()), new InventoryTransformer());
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
        return $this->response->item($this->inventoryService->get($id), new InventoryTransformer());
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
     */
    public function destroy(int $id)
    {
        $request = new DeleteInventoryRequest(['id' => $id]);

        if ($request->validate() && $this->parts->delete(['id' => $id])) {
            return $this->response->noContent();
        }

        return $this->response->errorBadRequest();
    }
}

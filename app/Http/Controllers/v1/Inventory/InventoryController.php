<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Transformers\Inventory\InventoryTransformer;

class InventoryController extends RestfulController
{
    
    protected $inventory;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(InventoryRepositoryInterface $inventory)
    {
        $this->inventory = $inventory;
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
            return $this->response->paginator($this->inventory->getAll($request->all(), true, true), new InventoryTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function show(int $id) { 
        return $this->response->item($this->inventory->get(['id' => $id]), new InventoryTransformer());
    }

}

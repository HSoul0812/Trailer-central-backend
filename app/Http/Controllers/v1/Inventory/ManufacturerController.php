<?php

namespace App\Http\Controllers\v1\Inventory;

use Dingo\Api\Http\Request;

use App\Http\Controllers\RestfulController;
use App\Repositories\Inventory\ManufacturerRepositoryInterface;
use App\Http\Requests\Inventory\GetManufacturersRequest;
use App\Transformers\Inventory\ManufacturerTransformer;

class ManufacturerController extends RestfulController
{
    
    protected $manufacturer;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ManufacturerRepositoryInterface $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }
    
    /**
     * @OA\Get(
     *     path="/api/inventory/manufacturers",
     *     description="Retrieve a list of inventory manufacturers",
     
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search String",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventory manufacturers",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetManufacturersRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->paginator($this->manufacturer->getAll($request->all(), true, true), new ManufacturerTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

}

<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\ManufacturerRepositoryInterface;
use App\Http\Requests\Parts\GetManufacturersRequest;
use App\Transformers\Parts\ManufacturerTransformer;

class ManufacturerController extends RestfulController
{
    
    protected $manufacturers;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ManufacturerRepositoryInterface $manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }
    
     /**
     * @OA\Get(
     *     path="/api/manufacturers",
     *     description="Retrieve a list of manufacturers",     
     *     tags={"Manufacturers"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="path",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="path",
     *         description="Dealer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
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
    public function index(Request $request) 
    {
        $request = new GetManufacturersRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->manufacturers->getAll($request->all()), new ManufacturerTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

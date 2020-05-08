<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use App\Repositories\Parts\BrandRepositoryInterface;
use App\Http\Requests\Parts\GetBrandsRequest;
use App\Transformers\Parts\BrandTransformer;

class BrandController extends RestfulController
{
    
    protected $brands;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BrandRepositoryInterface $brands)
    {
        $this->brands = $brands;
    }
    
    /**
     * @OA\Get(
     *     path="/api/parts/brands",
     *     description="Retrieve a list of brands",     
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),    
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Brand name to search",
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
    public function index(Request $request) 
    {
        $request = new GetBrandsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->brands->getAll($request->all()), new BrandTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\BinRepositoryInterface;
use App\Http\Requests\Parts\GetBinsRequest;
use App\Transformers\Parts\BinTransformer;

class BinController extends RestfulController
{
    
    protected $bins;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BinRepositoryInterface $bins)
    {
        $this->bins = $bins;
    }
    
    /**
     * @OA\Get(
     *     path="/api/parts/bins",
     *     description="Retrieve a list of bins",     
     *     tags={"Bins"},
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
     *         name="bin_name",
     *         in="query",
     *         description="Bin name to search",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),  
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of bins",
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
        $request = new GetBinsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->bins->getAll($request->all()), new BinTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

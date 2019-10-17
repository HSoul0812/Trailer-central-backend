<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\TypeRepositoryInterface;
use App\Http\Requests\Parts\GetTypesRequest;
use App\Transformers\Parts\TypeTransformer;

class TypeController extends RestfulController
{
    
    protected $types;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TypeRepositoryInterface $types)
    {
        $this->types = $types;
    }
    
     /**
     * @OA\Get(
     *     path="/api/parts/types",
     *     description="Retrieve a list of types",     
     *     tags={"Types"},
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
        $request = new GetTypesRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->types->getAll($request->all()), new TypeTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\VendorRepositoryInterface;
use App\Transformers\Parts\VendorTransformer;
use App\Http\Requests\Parts\GetVendorsRequest;

/**
 * @todo migrate outside Parts
 */
class VendorController extends RestfulController
{
    
    protected $vendors;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(VendorRepositoryInterface $vendors)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->vendors = $vendors;
    }
    
     /**
     * @OA\Get(
     *     path="/api/vendors",
     *     description="Retrieve a list of vendors",     
     *     tags={"Vendor"},
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
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of vendors",
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
        $request = new GetVendorsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->vendors->getAll($request->all()), new VendorTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

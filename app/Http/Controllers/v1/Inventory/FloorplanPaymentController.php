<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Inventory\FloorplanPaymentRepositoryInterface;
use App\Transformers\Inventory\FloorplanPaymentTransformer;
use App\Http\Requests\Inventory\GetFloorplanPaymentRequest;

class FloorplanPaymentController extends RestfulController
{
    
    protected $floorplanPayment;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FloorplanPaymentRepositoryInterface $floorplanPayment)
    {
        $this->floorplanPayment = $floorplanPayment;
    }
   

    /**
     * @OA\Get(
     *     path="/api/inventory/floorplan/payments",
     *     description="Retrieve a list of floorplan payments for specific dealer",
     *
     *     tags={"Floorplan Payments"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer Id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search String",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
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
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of floorplan payments for specific dealer",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetFloorplanPaymentRequest($request->all());
        
        if ( $request->validate() ) {
            if ($request->has('search_term')) {
                $floorplanPayments = $this->floorplanPayment->getAllSearch($request->all());
            } else {
                $floorplanPayments = $this->floorplanPayment->getAll($request->all());
            }
            
            return $this->response->paginator($floorplanPayments, new FloorplanPaymentTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

}

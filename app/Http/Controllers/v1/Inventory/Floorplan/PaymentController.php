<?php

namespace App\Http\Controllers\v1\Inventory\Floorplan;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;
use App\Transformers\Inventory\Floorplan\PaymentTransformer;
use App\Http\Requests\Inventory\Floorplan\GetPaymentRequest;

class PaymentController extends RestfulController
{
    
    protected $payment;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PaymentRepositoryInterface $payment)
    {
        $this->payment = $payment;
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
        $request = new GetPaymentRequest($request->all());
        
        if ( $request->validate() ) {
            $payments = $this->payment->getAll($request->all());
            return $this->response->paginator($payments, new PaymentTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

}

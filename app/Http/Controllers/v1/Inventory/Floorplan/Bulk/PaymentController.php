<?php

namespace App\Http\Controllers\v1\Inventory\Floorplan\Bulk;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request; 
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;
use App\Transformers\Inventory\Floorplan\PaymentTransformer;
use App\Http\Requests\Inventory\Floorplan\Bulk\CreatePaymentsRequest;

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
     * @OA\Put(
     *     path="/api/inventory/floorplan/bulk/payments",
     *     description="Create a floorplan payment
     *     tags={"Floorplan Payments"},
     *     @OA\Parameter(
     *         name="inventory_id",
     *         in="query",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Payment Type, one of balance or interest",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="account_id",
     *         in="query",
     *         description="Bank Account ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="Payment Amount",
     *         required=true,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="payment_type",
     *         in="query",
     *         description="Payment Method",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="check_number",
     *         in="query",
     *         description="Check Number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a floorplan payment created",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreatePaymentsRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->collection($this->payment->createBulk($request->all()['payments']), new PaymentTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

}

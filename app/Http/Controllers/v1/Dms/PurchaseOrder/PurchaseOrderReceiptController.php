<?php

namespace App\Http\Controllers\v1\Dms\PurchaseOrder;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;
use App\Transformers\Dms\PurchaseOrder\PurchaseOrderReceiptTransformer;
use App\Http\Requests\Dms\PurchaseOrder\GetPoReceiptRequest;
use App\Http\Requests\Dms\PurchaseOrder\ShowPoReceiptRequest;

/**
 * @author Marcel
 */
class PurchaseOrderReceiptController extends RestfulController
{

    protected $poReceiptRepository;

    protected $poReceiptTransformer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PurchaseOrderReceiptRepositoryInterface $poReceiptRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->poReceiptRepository = $poReceiptRepository;
        $this->poReceiptTransformer = new PurchaseOrderReceiptTransformer();
    }

    /**
     * @OA\Get(
     *     path="/api/dms/po-receipts",
     *     description="Retrieve a list of purchase order receipts",
     *     tags={"Purchase Orders"},
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
     *         name="vendor_id",
     *         in="query",
     *         description="Vendor ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of purchase order receipts",
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
        $request = new GetPoReceiptRequest($request->all());

        if ($request->validate()) {
          return $this->response->paginator($this->poReceiptRepository->getAll($request->all()), $this->poReceiptTransformer);
        }

        return $this->response->errorBadRequest();
    }

    public function show($id)
    {
        $request = new ShowPoReceiptRequest(['id' => $id]);

        if ( $request->validate() ) {
            return $this->response->item($this->poReceiptRepository->get(['id' => $id]), $this->poReceiptTransformer);
        }

        return $this->response->errorBadRequest();
    }

}

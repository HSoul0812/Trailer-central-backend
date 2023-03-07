<?php

namespace App\Http\Controllers\v1\Inventory\Floorplan;

use Dingo\Api\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\RestfulController;
use App\Http\Requests\Inventory\Floorplan\CheckNumberPaymentRequest;
use App\Transformers\Quickbooks\ExpenseTransformer;
use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\Inventory\Floorplan\PaymentTransformer;
use App\Http\Requests\Inventory\Floorplan\GetPaymentRequest;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\Floorplan\PaymentServiceInterface;
use App\Http\Requests\Inventory\Floorplan\CreatePaymentRequest;
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;

class PaymentController extends RestfulController
{
    protected $payment;

    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PaymentRepositoryInterface $payment,
        PaymentServiceInterface $paymentService,
        InventoryRepositoryInterface $inventoryRepository
    )
    {
        $this->payment = $payment;
        $this->paymentService = $paymentService;
        $this->inventoryRepository = $inventoryRepository;

        $this->middleware('setDealerIdOnRequest')->only(['create', 'downloadCsv', 'checkNumberExists']);
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
            if ($request->inventory_id) {
                $payments = $this->payment->getByInventory($request->all());
            } else {
                $payments = $this->payment->getAll($request->all());
            }

            return $this->response->paginator($payments, new PaymentTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/inventory/floorplan/payments",
     *     description="Create a floorplan payment",
     *     tags={"Floorplan Payments"},
     *     @OA\Parameter(
     *         name="inventory_id",
     *         in="query",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
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
     *         description="Error: Bad request."
     *     )
     * )
     */
    public function create(Request $request) {
        $request = new CreatePaymentRequest($request->all());

        if ( $request->validate() ) {
            $expense = $this->paymentService->create($request->all());

            return $this->response->item($expense, new ExpenseTransformer());
        }

        return $this->response->errorBadRequest();
    }

    public function downloadCsv(Request $request)
    {
        $request = new GetInventoryRequest($request->all());

        if ($request->validate()) {
            $requestArray = $request->all();
            $inventories = $this->inventoryRepository->getFloorplannedInventory($requestArray, false);

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=floorplans.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
            $columns = array('Date Floorplan', 'Date Sold', 'Location', 'Stock #', 'VIN', 'Status', 'Make', 'Title', 'Cost', 'Balance Remaining', 'Interest Paid', 'Balance Payment', 'Interest Payment', 'Floorplan Vendor');

            $callback = function() use ($columns, $inventories ) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($inventories as $inventory) {
                    $inventoryTransformer = new InventoryTransformer();
                    $floorPlanInventory = $inventoryTransformer->transform($inventory);

                    fputcsv($file, [
                        $floorPlanInventory['created_at'] ? $floorPlanInventory['created_at']->format('d M, Y') : '',
                        $floorPlanInventory['sold_at'] ? $floorPlanInventory['sold_at']->format('d M, Y') : '',
                        $floorPlanInventory['dealer_location'] ? trim($floorPlanInventory['dealer_location']['name']) : '',
                        $floorPlanInventory['stock'] ?? '',
                        $floorPlanInventory['vin'] ?? '',
                        $floorPlanInventory['status'] ?? '',
                        $floorPlanInventory['model'] ?? '',
                        $floorPlanInventory['title'] ?? '',
                        $floorPlanInventory['true_cost'] ?? '0.00',
                        $floorPlanInventory['fp_balance'] ?? '0.00',
                        $floorPlanInventory['fp_interest_paid'] ?? '0.00',
                        '',
                        '',
                        $floorPlanInventory['floorplan_vendor'] ? $floorPlanInventory['floorplan_vendor']->name : '',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/floorplan/payments/check-number-exists",
     *     description="Checks whether an check number payment exists",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="check_number",
     *         in="query",
     *         description="Check Number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a result",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param CheckNumberPaymentRequest $request
     * @return Response
     * @throws NoObjectIdValueSetException|NoObjectTypeSetException
     */
    public function checkNumberExists(Request $request): Response
    {
        $checkNumberPaymentRequest = new CheckNumberPaymentRequest($request->all());

        if (!$checkNumberPaymentRequest->validate()) {
            return $this->response->errorBadRequest();
        }

        $isExists = $this->paymentService->checkNumberExists(
            $request->input('dealer_id'),
            $request->input('checkNumber')
        );

        return $this->existsResponse($isExists);
    }
}

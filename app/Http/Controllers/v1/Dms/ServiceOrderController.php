<?php

namespace App\Http\Controllers\v1\Dms;

use App\Http\Controllers\RestfulController;
use App\Http\Controllers\RestfulControllerV2;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Transformers\Dms\ServiceOrderTransformer;
use App\Http\Requests\Dms\GetServiceOrdersRequest;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use OpenApi\Annotations as OA;
use App\Http\Requests\Dms\ServiceOrder\UpdateServiceOrderRequest;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @author Marcel
 */
class ServiceOrderController extends RestfulControllerV2
{

    protected $serviceOrders;
    /**
     * @var Manager
     */
    private $fractal;
    /**
     * @var ServiceOrderTransformer
     */
    private $transformer;

    /**
     * Create a new controller instance.
     *
     * @param ServiceOrderRepositoryInterface $serviceOrders
     * @param ServiceOrderTransformer $transformer
     * @param Manager $fractal
     */
    public function __construct(
        ServiceOrderRepositoryInterface $serviceOrders,
        ServiceOrderTransformer $transformer,
        Manager $fractal
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'update']);
        $this->serviceOrders = $serviceOrders;

        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());
        $this->transformer = $transformer;
    }

    /**
     * @OA\Get(
     *     path="/api/dms/service-orders",
     *     description="Retrieve a list of service orders",
     *     tags={"Service Orders"},
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
     *         name="status",
     *         in="query",
     *         description="Status of service order",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Returns a list of service orders",
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
        try {
            $request = new GetServiceOrdersRequest($request->all());
            $this->fractal->parseIncludes($request->query('with', ''));

            if ($request->validate()) {
                return $this->response->paginator(
                    $this->serviceOrders->getAll($request->all()),
                    new ServiceOrderTransformer
                );
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return $this->response->errorBadRequest();
    }

    public function show($id, Request $request)
    {
        $this->fractal->setSerializer(new NoDataArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        $serviceOrder = $this->serviceOrders->get(['id' => $id]);
        $data = new Item($serviceOrder, $this->transformer);

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/dms/service-order/{id}",
     *     description="Updates a given service order",
     *     tags={"Service Orders"},
     *   @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status of service order",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the updated service order",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     )
     * )
     *
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateServiceOrderRequest($requestData);

        if ($request->validate()) {
            return $this->response->item($this->serviceOrders->update($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Service for create/update invoice for an RO
     * @param $id
     * @param  Request  $request
     */
    public function createInvoice($id, Request $request)
    {

    }
}

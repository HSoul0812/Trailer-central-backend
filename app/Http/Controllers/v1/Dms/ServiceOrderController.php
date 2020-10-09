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
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->serviceOrders = $serviceOrders;
        $this->fractal = $fractal;
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
        $request = new GetServiceOrdersRequest($request->all());

        if ($request->validate()) {
          return $this->response->paginator($this->serviceOrders->getAll($request->all()), new ServiceOrderTransformer);
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

}

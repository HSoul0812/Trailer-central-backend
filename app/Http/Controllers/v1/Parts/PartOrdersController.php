<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\PartOrderRepositoryInterface;
//use App\Http\Requests\Parts\CreatePartOrderRequest;
//use App\Http\Requests\Parts\DeletePartOrderRequest;
use App\Http\Requests\Parts\ShowPartOrderRequest;
use App\Http\Requests\Parts\GetPartOrdersRequest;
//use App\Http\Requests\Parts\UpdatePartOrderRequest;
use App\Transformers\Parts\PartOrdersTransformer;

class PartOrdersController extends RestfulController
{

    /**
     * @var App\Repositories\Parts\PartOrderRepositoryInterface
     */
    protected $partOrders;

    /**
     * @var App\Services\Parts\PartOrderServiceInterface;
     */
    protected $partOrderService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PartOrderRepositoryInterface $partOrders)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update', 'index']);
        $this->middleware('setWebsiteIdOnRequest')->only(['create', 'update']);
        $this->partOrders = $partOrders;
    }

    /**
     * @OA\Put(
     *     path="/api/parts-order/",
     *     description="Create a part",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *
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
    public function create(Request $request) {
        $request = new CreatePartOrderRequest($request->all());
        $requestData = $request->all();

        if ( $request->validate() ) {
            return $this->response->item($this->partOrders->create($requestData), new PartOrdersTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/parts-order/{id}",
     *     description="Delete a part",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms part was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function destroy(int $id) {
        $request = new DeletePartOrderRequest(['id' => $id]);

        if ( $request->validate() && $this->partOrders->delete(['id' => $id])) {
            return $this->response->noContent();
        }

        return $this->response->errorBadRequest();
    }


    /**
     * @OA\Get(
     *     path="/api/parts-order",
     *     description="Retrieve a list of parts",

     *     tags={"Parts"},
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
    public function index(Request $request) {
        $request = new GetPartOrdersRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->partOrders->getAll($request->all()), new PartOrdersTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/parts-order/{id}",
     *     description="Retrieve a part",

     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a part",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function show(int $id) {
        $request = new ShowPartOrderRequest(['id' => $id]);

        if ( $request->validate() ) {
            return $this->response->item($this->partOrders->get(['id' => $id]), new PartOrdersTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(
     *     path="/api/parts-order/{id}",
     *     description="Update a part",

     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
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
    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdatePartOrderRequest($requestData);
        $requestData = $request->all();

        if ( $request->validate() ) {
            return $this->response->item($this->partOrders->update($requestData), new PartOrdersTransformer());
        }

        return $this->response->errorBadRequest();
    }

}

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
     *     path="/api/parts/",
     *     description="Create a part",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         description="Part subcategory",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *   @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Part title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *   @OA\Parameter(
     *         name="alternative_part_number",
     *         in="query",
     *         description="Alternative Part Number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Part sku",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Part price",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_cost",
     *         in="query",
     *         description="Part dealer cost",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="msrp",
     *         in="query",
     *         description="Part msrp",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight",
     *         in="query",
     *         description="Part weight",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight_rating",
     *         in="query",
     *         description="Part weight rating",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Part description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="qty",
     *         in="query",
     *         description="Part qty",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="show_on_website",
     *         in="query",
     *         description="Part show on website",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_vehicle_specific",
     *         in="query",
     *         description="Part vehicle specific",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_make",
     *         in="query",
     *         description="Part vehicle make",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_model",
     *         in="query",
     *         description="Part vehicle model",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_from",
     *         in="query",
     *         description="Part vehicle year from",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_to",
     *         in="query",
     *         description="Part vehicle year to",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="video_embed_code",
     *         in="query",
     *         description="Video embed code",
     *         required=false,
     *         @OA\Schema(type="text")
     *     ),
     *    @OA\Parameter(
     *         name="images",
     *         in="query",
     *         description="Part images",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Image URL array"
     *         )
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
     *     path="/api/parts/{id}",
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
     *     path="/api/parts",
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
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part IDs",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Part IDs array"
     *         )
     *     ),
     *   @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Part price can be in format: [10 TO 100], [10], [10.0 TO 100.0]",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *     path="/api/parts/{id}",
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
     *     path="/api/parts/{id}",
     *     description="Update a part",

     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         description="Part subcategory",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *   @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Part title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *   @OA\Parameter(
     *         name="alternative_part_number",
     *         in="query",
     *         description="Alternative Part Number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Part sku",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Part price",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_cost",
     *         in="query",
     *         description="Part dealer cost",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="msrp",
     *         in="query",
     *         description="Part msrp",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight",
     *         in="query",
     *         description="Part weight",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight_rating",
     *         in="query",
     *         description="Part weight rating",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Part description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="qty",
     *         in="query",
     *         description="Part qty",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="show_on_website",
     *         in="query",
     *         description="Part show on website",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_vehicle_specific",
     *         in="query",
     *         description="Part vehicle specific",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_make",
     *         in="query",
     *         description="Part vehicle make",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_model",
     *         in="query",
     *         description="Part vehicle model",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_from",
     *         in="query",
     *         description="Part vehicle year from",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_to",
     *         in="query",
     *         description="Part vehicle year to",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="video_embed_code",
     *         in="query",
     *         description="Video embed code",
     *         required=false,
     *         @OA\Schema(type="text")
     *     ),
     *    @OA\Parameter(
     *         name="images",
     *         in="query",
     *         description="Part images",
     *         required=false,
     *          @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Image URL array"
     *         )
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

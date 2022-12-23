<?php

namespace App\Http\Controllers\v1\Parts;

use App\Domains\ElasticSearch\Actions\EscapeElasticSearchReservedCharactersAction;
use App\Http\Controllers\RestfulController;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Http\Requests\Parts\CreatePartRequest;
use App\Http\Requests\Parts\DeletePartRequest;
use App\Transformers\Parts\PartsTransformer;
use App\Http\Requests\Parts\ShowPartRequest;
use App\Http\Requests\Parts\GetPartsRequest;
use App\Http\Requests\Parts\UpdatePartRequest;
use App\Models\Parts\Part;
use App\Services\Parts\PartServiceInterface;
use App\Transformers\Parts\PartsTransformerInterface;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class PartsController extends RestfulController
{

    protected $parts;

    /**
     * @var \App\Services\Parts\PartServiceInterface;
     */
    protected $partService;
    /**
     * @var Manager
     */
    private $fractal;
    /**
     * @var PartsTransformer
     */
    protected $partsTransformer;

    /**
     * Create a new controller instance.
     *
     * @param  PartRepositoryInterface  $parts
     * @param  PartServiceInterface  $partService
     * @param  Manager  $fractal
     */
    public function __construct(PartRepositoryInterface $parts, PartServiceInterface $partService, Manager $fractal, PartsTransformerInterface $partsTransformer)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update']);
        $this->middleware(SubstituteBindings::class)->only(['display']);
        $this->parts = $parts;
        $this->partService = $partService;
        $this->fractal = $fractal;
        $this->partsTransformer = $partsTransformer;
    }

    /**
     * @OA\Post(
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
     *         name="vendor_id",
     *         in="query",
     *         description="Vendor ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_specific_id",
     *         in="query",
     *         description="Vehicle Specific ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),     *
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Part brand",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="manufacturer_id",
     *         in="query",
     *         description="Part manufacturers",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type_id",
     *         in="query",
     *         description="Part type",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Part category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="qb_id",
     *         in="query",
     *         description="Part quickbooks id",
     *         required=false,
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
     *         description="Part average cost",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="latest_cost",
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
     *    @OA\Parameter(
     *         name="bins",
     *         in="query",
     *         description="Part bins",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Bin array with bin_id and name"
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
        $requestData = $request->all();
        $request = new CreatePartRequest($requestData);
        if ( $request->validate() ) {
            $requestData['subcategory'] = $requestData['subcategory'] ?? '';

            return $this->response->item(
                $this->partService->create($requestData, !empty($requestData['bins'])
                    ? $requestData['bins'] : []), new PartsTransformer()
            );
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
        $request = new DeletePartRequest(['id' => $id]);

        if ( $request->validate() && $this->parts->delete(['id' => $id])) {
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
     *         name="type_id",
     *         in="query",
     *         description="Part types",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Type ID arra"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Part categories",
     *         required=false,
     *          @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Category ID array"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="manufacturer_id",
     *         in="query",
     *         description="Part manufacturers",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Manufacturer ID array"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Part brands",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Brand ID array"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bin_id",
     *         in="query",
     *         description="Part Bins",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="An array of bin IDs"
     *         )
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
        $request = new GetPartsRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->parts->getAll($request->all()), $this->partsTransformer);
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
     *         in="path",
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
    public function show($id)
    {
        $request = new ShowPartRequest(['id' => $id]);

        if ($request->validate()) {
            return $this->response->item($this->parts->get(['id' => $id]), new PartsTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/parts/{id}",
     *     description="Update a part",

     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Part ID",
     *         required=true,
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
     *         name="vehicle_specific_id",
     *         in="query",
     *         description="Vehicle Specific ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),     *
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Part brand",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="manufacturer_id",
     *         in="query",
     *         description="Part manufacturers",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type_id",
     *         in="query",
     *         description="Part type",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         description="Part category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="qb_id",
     *         in="query",
     *         description="Part quickbooks id",
     *         required=false,
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
     *         description="Part average cost",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="latest_cost",
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
        $request = new UpdatePartRequest($requestData);
        $requestData = $request->all();

        if ( $request->validate() ) {
            return $this->response->item($this->partService->update($requestData, !empty($requestData['bins']) ? $requestData['bins'] : []), new PartsTransformer());
        }

        return $this->response->errorBadRequest();
    }

    public function search(Request $request)
    {
        try {
            $this->fractal->setSerializer(new NoDataArraySerializer());
            $this->fractal->parseIncludes($request->query('with', ''));

            // We want to make sure that the query string is escaped
            // If we don't do this we will get error when we try to search
            // with special characters like '/', '(', etc.
            $escapedQuery = resolve(EscapeElasticSearchReservedCharactersAction::class)->execute($request->get('query', '') ?? '');
            $request->merge(['query' => $escapedQuery]);

            $query = $request->only('query', 'vendor_id', 'with_cost', 'in_stock', 'sort', 'is_active');

            $paginator = new \stdClass(); // this will hold the paginator produced by search
            $dealerId = $this->getRequestDealerId(Auth::user());

            // do the search
            $result = $this->parts->search(
                $query, $dealerId, [
                    'allowAll' => true,
                    'page' => $request->get('page'),
                    'per_page' => $request->get('per_page', 10),
                ], $paginator
            );
            $data = new Collection($result, $this->partsTransformer, 'data');

            // if a paginator is requested
            if ($request->get('page')) {
                $data->setPaginator(new IlluminatePaginatorAdapter($paginator));
            }

            // parses the include params
            $this->fractal->parseIncludes($request->get('include', []));

            // build the api response
            $result = (array) $this->fractal->createData($data)->toArray();
            return $this->response->array($result);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->response->errorBadRequest($e->getMessage());
        }
    }

    private function getRequestDealerId($user, $required = true)
    {
        if (!empty($user) && !empty($user->dealer_id)) {
            return $user->dealer_id;
        }

        if ($required) {
            throw new \Exception('Dealer is required');
        }

        return null;
    }

    public function display(Part $part)
    {
        $part->load('bins.bin');

        return $this->response->item($part, new PartsTransformer());
    }
}

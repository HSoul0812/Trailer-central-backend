<?php

namespace App\Http\Controllers\v1\Parts\Textrail;

use App\Http\Controllers\v1\Parts\PartsController as BasePartsController;
use App\Http\Requests\Parts\Textrail\GetPartsRequest;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use Dingo\Api\Http\Response;

class PartsController extends BasePartsController
{
   /**
    *
    * @param Request $request
    * @return \Dingo\Api\Http\Response
    * @throws NotImplementedException
    */
    public function create(Request $request):Response {
        throw new NotImplementedException();
    }

    /**
     *
     * @param int $id
     * @return \Dingo\Api\Http\Response
     */
    public function destroy(int $id):Response {
        throw new NotImplementedException();
    }

    /**
     *
     * @param int $id
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws NotImplementedException
     */
    public function update(int $id, Request $request):Response {
        throw new NotImplementedException();
    }

    /**
     * @OA\Get(
     *     path="/api/textrail/parts",
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
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length,sku,-sku,dealer_cost,-dealer_cost,msrp,-msrp,subcategory,-subcategory,created_at,-created_at,stock,-stock",
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
     *            description="Type ID array"
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
}

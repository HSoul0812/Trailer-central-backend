<?php

namespace App\Http\Controllers\v1\Website\Forms;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Website\Forms\FieldMapRepositoryInterface;
use App\Http\Requests\Website\Forms\GetFieldMapRequest;

class FieldMapController extends RestfulController
{
    
    protected $fields;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FieldMapRepositoryInterface $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     description="Retrieve a list of posts",
     
     *     tags={"Post"},
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
     *         description="Post types",
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
     *         description="Post categories",
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
     *         description="Post manufacturers",
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
     *         description="Post brands",
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
     *         name="id",
     *         in="query",
     *         description="Post IDs",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Post IDs array"
     *         )
     *     ),
     *   @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Post price can be in format: [10 TO 100], [10], [10.0 TO 100.0]",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of posts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetFieldMapRequest($request->all());
        
        if ( $request->validate() ) {
            $fields = $this->posts->getAll($request->all());
            return $this->response->array([
                'data' => $fields
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;

use App\Repositories\Inventory\CategoryRepositoryInterface;
use App\Http\Requests\Inventory\GetCategoryRequest;
use App\Transformers\Inventory\CategoryTransformer;

class CategoryController extends RestfulController
{

    protected $category;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CategoryRepositoryInterface $category)
    {
        $this->category = $category;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/categories",
     *     description="Retrieve a list of inventory categories",

     *     tags={"Inventory Category"},
     *     @OA\Parameter(
     *         name="entity_type_id",
     *         in="query",
     *         description="Entity type id to filter",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: label,-label,title,-title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventory categories",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetCategoryRequest($request->all());

        if ( $request->validate() ) {
            return $this->response->collection($this->category->getAll($request->all()), new CategoryTransformer());
        }

        return $this->response->errorBadRequest();
    }
}

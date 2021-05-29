<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\CategoryRepositoryInterface;
use App\Http\Requests\Parts\GetCategoriesRequest;
use App\Transformers\Parts\CategoryTransformer;
use Dingo\Api\Http\Response;

class CategoryController extends RestfulController
{

    /** @var CategoryRepositoryInterface  */
    protected $categories;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

     /**
     * @OA\Get(
     *     path="/api/parts/categories",
     *     description="Retrieve a list of categories",
     *     tags={"Categories"},
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
    public function index(Request $request)
    {
        $request = new GetCategoriesRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->categories->getAll($request->all()), new CategoryTransformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/parts/categories/{id}",
     *     description="Retrieve a category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a item",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Parameter id is required",
     *     ),
     * )
     *
     * @param int $id part id
     * @return Response
     */
    public function show(int $id): Response
    {
        return $this->response->item($this->categories->get(['id' => $id]), new CategoryTransformer());
    }
}

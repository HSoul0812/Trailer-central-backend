<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\CategoryRepositoryInterface;
use App\Http\Requests\Parts\GetCategoriesRequest;
use App\Transformers\Parts\CategoryTransformer;

class CategoryController extends RestfulController
{
    
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
     *     path="/api/categories",
     *     description="Retrieve a list of categories",     
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="path",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="path",
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
    
}

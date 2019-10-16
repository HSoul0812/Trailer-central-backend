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
     * Displays a list of all records in the DB. 
     * Paginated or not paginated
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

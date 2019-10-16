<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use App\Repositories\Parts\BrandRepositoryInterface;
use App\Http\Requests\Parts\GetBrandsRequest;
use App\Transformers\Parts\BrandTransformer;

class BrandController extends RestfulController
{
    
    protected $brands;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BrandRepositoryInterface $brands)
    {
        $this->brands = $brands;
    }
    
    /**
     * Displays a list of all records in the DB. 
     * Paginated or not paginated
     */
    public function index(Request $request) 
    {
        $request = new GetBrandsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->brands->getAll($request->all()), new BrandTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

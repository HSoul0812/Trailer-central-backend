<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\TypeRepositoryInterface;
use App\Http\Requests\Parts\GetTypesRequest;
use App\Transformers\Parts\TypeTransformer;

class TypeController extends RestfulController
{
    
    protected $types;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TypeRepositoryInterface $types)
    {
        $this->types = $types;
    }
    
    /**
     * Displays a list of all records in the DB. 
     * Paginated or not paginated
     */
    public function index(Request $request) 
    {
        $request = new GetTypesRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->types->getAll($request->all()), new TypeTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

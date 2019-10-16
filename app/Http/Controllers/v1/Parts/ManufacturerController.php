<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\ManufacturerRepositoryInterface;
use App\Http\Requests\Parts\GetManufacturersRequest;
use App\Transformers\Parts\ManufacturerTransformer;

class ManufacturerController extends RestfulController
{
    
    protected $manufacturers;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ManufacturerRepositoryInterface $manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }
    
    /**
     * Displays a list of all records in the DB. 
     * Paginated or not paginated
     */
    public function index(Request $request) 
    {
        $request = new GetManufacturersRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->manufacturers->getAll($request->all()), new ManufacturerTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

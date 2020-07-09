<?php

namespace App\Http\Controllers\v1\Dms\Customer;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Http\Requests\Dms\GetCustomersRequest;
use App\Transformers\Dms\CustomerTransformer;

class CustomerController extends RestfulController
{
    
    protected $leads;
    
    protected $transformer;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LeadRepositoryInterface $leadRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->leads = $leadRepo;
        $this->transformer = new CustomerTransformer;
    }
    
    public function index(Request $request) 
    {
        $request = new GetCustomersRequest($request->all());
        
        if ($request->validate()) {
            /**
             * Need to migrate lead customers to dms_customer and pull from there
             */
            return $this->response->paginator($this->leads->getCustomers($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}

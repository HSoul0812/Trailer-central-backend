<?php

namespace App\Http\Controllers\v1\Dms\Customer;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Http\Requests\Dms\Customer\GetOpenBalanceRequest;
use App\Transformers\Dms\Customer\OpenBalanceTransformer;
use Dingo\Api\Http\Request;

class OpenBalanceController extends RestfulControllerV2 
{
    /**     
     * @var App\Repositories\CRM\Customer\CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    protected $transformer;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->customerRepository = $customerRepository;
        $this->transformer = new OpenBalanceTransformer;
    }
    
    public function index(Request $request) 
    {        
        $request = new GetOpenBalanceRequest($request->all());
        if ($request->validate()) {
            return $this->response->collection($this->customerRepository->getCustomersWihOpenBalance($request->dealer_id, $request->per_page ? $request->per_page : 15), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
}

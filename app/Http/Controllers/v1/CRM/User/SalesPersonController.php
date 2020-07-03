<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\User\GetSalesPeopleRequest;
use App\Transformers\CRM\User\SalesPersonTransformer;

class SalesPersonController extends RestfulController {
    
    protected $salesPerson;
    
    protected $transformer;
    
    public function __construct(SalesPersonRepositoryInterface $salesPersonRepo) {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->salesPerson = $salesPersonRepo;
    }
    
    public function index(Request $request) {
        $request = new GetSalesPeopleRequest($request->all());
        if ($request->validate()) {
            return $this->response->paginator($this->salesPerson->getAll($request->all()), new SalesPersonTransformer);
        }
        return $this->response->errorBadRequest();
    }
}

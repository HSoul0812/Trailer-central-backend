<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\GetLeadsSourceRequest;

class LeadSourceController extends RestfulController
{
    protected $leads;

    /**
     * Create a new controller instance.
     *
     * @param Repository $leads
     */
    public function __construct(LeadRepositoryInterface $leads)
    {
        $this->leads = $leads;
    }

    public function index(Request $request) {
        $request = new GetLeadsSourceRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->array([
                'data' => $this->leads->getSources()
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

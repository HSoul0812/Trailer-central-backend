<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\GetLeadsStatusRequest;

class LeadStatusController extends RestfulController
{
    protected $leads;

    /**
     * Create a new controller instance.
     *
     * @param Repository $status
     */
    public function __construct(StatusRepositoryInterface $status)
    {
        $this->status = $status;
    }

    public function index(Request $request) {
        $request = new GetLeadsStatusRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->array([
                'data' => $this->status->getAll($request->all())
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

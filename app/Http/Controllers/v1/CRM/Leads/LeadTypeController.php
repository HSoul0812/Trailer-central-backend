<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\GetLeadsTypeRequest;

class LeadTypeController extends RestfulController
{
    protected $types;

    /**
     * Create a new controller instance.
     *
     * @param Repository $types
     */
    public function __construct(TypeRepositoryInterface $types)
    {
        $this->types = $types;
    }

    public function index(Request $request) {
        $request = new GetLeadsTypeRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->array([
                'data' => $this->types->getAllUnique()
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

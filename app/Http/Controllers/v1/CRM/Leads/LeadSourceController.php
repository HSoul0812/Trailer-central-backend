<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\GetLeadsSourceRequest;

class LeadSourceController extends RestfulController
{
    protected $leads;

    /**
     * Create a new controller instance.
     *
     * @param Repository $sources
     */
    public function __construct(SourceRepositoryInterface $sources)
    {
        $this->sources = $sources;
    }

    public function index(Request $request) {
        $request = new GetLeadsSourceRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->array([
                'data' => $this->sources->getAll($request->all())
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

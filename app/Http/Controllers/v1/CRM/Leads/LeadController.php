<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\GetLeadsRequest;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Http\Requests\CRM\Leads\GetLeadsStatesRequest;
use App\Http\Requests\CRM\Leads\GetLeadsSortFieldsRequest;
use App\Http\Requests\CRM\Leads\UpdateLeadRequest;
use App\Http\Requests\CRM\Leads\CreateLeadRequest;

class LeadController extends RestfulController
{
    protected $leads;
    
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $interactions
     */
    public function __construct(LeadRepositoryInterface $leads)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'update', 'create']);
        $this->leads = $leads;
        $this->transformer = new LeadTransformer;
    }

    public function index(Request $request) {
        $request = new GetLeadsRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->paginator($this->leads->getAll($requestData), $this->transformer)
                        ->addMeta('lead_counts', $this->leads->getLeadStatusCountByDealer($requestData['dealer_id'], $requestData));
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function create(Request $request) {
        $request = new CreateLeadRequest($request->all());
        
        if ($request->validate()) {             
            return $this->response->item($this->leads->create($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateLeadRequest($requestData);
        
        if ($request->validate()) {
            return $this->response->item($this->leads->update($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function sortFields(Request $request) {
        $request = new GetLeadsSortFieldsRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->array([ 'data' => $this->leads->getLeadsSortFields() ]);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function states(Request $request) {
        $request = new GetLeadsStatesRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {             
            return $this->response->array([ 'data' => $this->leads->getStates() ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

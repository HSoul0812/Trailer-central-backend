<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\GetLeadsRequest;
use App\Transformers\CRM\Leads\LeadTransformer;

class LeadController extends RestfulController
{
    protected $leads;

    /**
     * Create a new controller instance.
     *
     * @param Repository $interactions
     */
    public function __construct(LeadRepositoryInterface $leads)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->leads = $leads;
    }

    public function index(Request $request) {
        $request = new GetLeadsRequest($request->all());
        $requestData = $request->all();
        
        if ($request->validate()) {             
            return $this->response->paginator($this->leads->getAll($requestData), new LeadTransformer)->addMeta('lead_counts', $this->leads->getLeadStatusCountByDealer($requestData['dealer_id']));
        }
        
        return $this->response->errorBadRequest();
    }
}

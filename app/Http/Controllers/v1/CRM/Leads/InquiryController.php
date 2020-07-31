<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Leads\InquiryRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Http\Requests\CRM\Leads\InquiryLeadRequest;

class LeadController extends RestfulController
{
    protected $inquiry;
    
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $leads
     */
    public function __construct(InquiryRepositoryInterface $inquiry)
    {
        $this->inquiry = $inquiry;
        $this->transformer = new LeadTransformer;
    }

    /**
     * Create Lead and Send Email Inquiry
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request) {
        $request = new InquiryLeadRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->inquiry->create($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}

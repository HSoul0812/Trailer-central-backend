<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Leads\InquiryLeadRequest;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Transformers\CRM\Leads\LeadTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class InquiryController extends RestfulController
{
    /**
     * @var App\Services\CRM\Leads\LeadServiceInterface
     */
    protected $leads;

    /**
     * @var App\Transformers\CRM\Leads\LeadTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param LeadServiceInterface $leads
     */
    public function __construct(LeadServiceInterface $leads)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
        $this->leads = $leads;
        $this->transformer = new LeadTransformer;
    }

    /**
     * Create Lead and Send Email Inquiry
     * 
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response {
        $request = new InquiryLeadRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->leads->inquiry($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}

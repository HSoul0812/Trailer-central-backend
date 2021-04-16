<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Leads\Inquiry\CreateInquiryRequest;
use App\Http\Requests\CRM\Leads\Inquiry\SendInquiryRequest;
use App\Services\CRM\Leads\InquiryServiceInterface;
use App\Transformers\CRM\Leads\LeadTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class InquiryController extends RestfulController
{
    /**
     * @var App\Services\CRM\Leads\InquiryServiceInterface
     */
    protected $inquiry;

    /**
     * @var App\Transformers\CRM\Leads\LeadTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param InquiryServiceInterface $inquiry
     */
    public function __construct(InquiryServiceInterface $inquiry, LeadTransformer $transformer)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'send']);
        $this->inquiry = $inquiry;
        $this->transformer = $transformer;
    }

    /**
     * Create Lead for Inquiry but DON'T Send Email
     * 
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response {
        $request = new CreateInquiryRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->inquiry->create($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Create Lead and Send Email Inquiry
     * 
     * @param Request $request
     * @return Response
     */
    public function send(Request $request): Response {
        $request = new SendInquiryRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->inquiry->send($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}

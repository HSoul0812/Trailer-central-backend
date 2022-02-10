<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Leads\Inquiry\CreateInquiryRequest;
use App\Http\Requests\CRM\Leads\Inquiry\SendInquiryRequest;
use App\Http\Requests\CRM\Leads\Inquiry\TextInquiryRequest;
use App\Services\CRM\Leads\InquiryServiceInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class InquiryController extends RestfulController
{
    /**
     * @var App\Services\CRM\Leads\InquiryServiceInterface
     */
    protected $inquiry;

    /**
     * Create a new controller instance.
     *
     * @param InquiryServiceInterface $inquiry
     */
    public function __construct(InquiryServiceInterface $inquiry)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'send']);
        $this->inquiry = $inquiry;
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
            return $this->response->array($this->inquiry->create($request->all()));
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
            return $this->response->array($this->inquiry->send($request->all()));
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Create Lead and Send Text Inquiry
     *
     * @param Request $request
     * @return Response
     */
    public function text(Request $request): Response {
        $request = new TextInquiryRequest($request->all());

        if ($request->validate()) {
            return $this->response->array($this->inquiry->text($request->all()));
        }

        return $this->response->errorBadRequest();
    }
}

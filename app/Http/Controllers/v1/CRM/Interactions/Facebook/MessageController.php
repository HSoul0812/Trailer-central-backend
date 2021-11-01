<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\SendMessageRequest;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;
use App\Transformers\CRM\Interactions\Facebook\MessageTransformer;
use Dingo\Api\Http\Request;

class MessageController extends RestfulController
{
    /**
     * @var MessageServiceInterface
     */
    protected $service;

    /**
     * @var MessageTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param MessageServiceInterface $service
     * @param MessageTransformer $transformer
     */
    public function __construct(MessageServiceInterface $service, MessageTransformer $transformer)
    {
        $this->service = $service;
        $this->transformer = $transformer;
    }

    /**
     * Send Message
     * 
     * @mode POST
     * @param int $leadId
     * @param Request $request
     * @return Response
     */
    public function send(int $leadId, Request $request) {
        // Convert to Request
        $request = new SendMessageRequest(array_merge(['lead_id' => $leadId], $request->all()));

        if ($request->validate()) {
            return $this->response->item($this->service->send($request), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}
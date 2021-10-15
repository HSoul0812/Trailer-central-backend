<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Services\CRM\Interactions\Facebook\WebhookServiceInterface;
use App\Transformers\CRM\Interactions\Facebook\MessageTransformer;
use Dingo\Api\Http\Request;

class FacebookController extends RestfulController
{
    /**
     * @var WebhookServiceInterface
     */
    protected $service;

    /**
     * @var MessageTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param WebhookServiceInterface $service
     * @param MessageTransformer $transformer
     */
    public function __construct(WebhookServiceInterface $service, MessageTransformer $transformer)
    {
        $this->service = $service;
        $this->transformer = $transformer;
    }

    public function message(Request $request) {
        $request = new MessageWebhookRequest($request->all());

        if ($request->validate()) {             
            return $this->response->paginator($this->service->message($request));
        }
        
        return $this->response->errorBadRequest();
    }
}
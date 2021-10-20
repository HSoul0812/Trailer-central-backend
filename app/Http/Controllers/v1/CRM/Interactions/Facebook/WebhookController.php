<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookVerify;
use App\Services\CRM\Interactions\Facebook\WebhookServiceInterface;
use App\Transformers\CRM\Interactions\Facebook\MessageTransformer;
use Dingo\Api\Http\Request;

class WebhookController extends RestfulController
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

    /**
     * Message Webhook
     * 
     * @mode POST
     * @param Request $request
     * @return Response
     */
    public function message(Request $request) {
        // Get JSON
        $json = json_decode($request->getContent(), true);

        // Convert to Request
        $request = new MessageWebhookRequest($json);

        if ($request->validate()) {
            return $this->response->collection($this->service->message($request), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Message Webhook Verify
     * 
     * @mode GET
     * @param Request $request
     * @return Response
     */
    public function verifyMessage(Request $request) {
        // Verify Request
        $request = new MessageWebhookVerify($request->all());

        if ($request->validate()) {
            return $this->response->text($this->service->verify($request));
        }

        return $this->response->errorBadRequest();
    }
}
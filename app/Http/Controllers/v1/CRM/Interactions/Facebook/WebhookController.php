<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
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

    public function message(Request $request) {
        // Get JSON
        $json = json_decode($request->getContent());

        // Convert to Request
        $request = new MessageWebhookRequest($json);

        if ($request->validate()) {
            return $this->response->collection($this->service->message($request), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}
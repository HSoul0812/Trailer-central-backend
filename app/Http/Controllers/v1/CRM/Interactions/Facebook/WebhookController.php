<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\MessageWebhookRequest;
use App\Services\CRM\Interactions\Facebook\WebhookServiceInterface;
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
    public function __construct(WebhookServiceInterface $service)
    {
        $this->service = $service;
    }

    public function message(Request $request) {
        // Get JSON
        $json = json_decode($request->getContent(), true);

        // Convert to Request
        $request = new MessageWebhookRequest($json);

        if ($request->validate()) {
            $this->service->message($request);
            return $this->response->item(WebhookServiceInterface::VALID_RESPONSE);
        }

        return $this->response->errorBadRequest();
    }
}
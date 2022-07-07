<?php

namespace App\Http\Controllers\v1\Webhook;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Webhook\Twilio\VerifySmsRequest;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Transformers\CRM\Text\NumberVerifyTransformer;

class TwilioController extends RestfulControllerV2 {
    /**
     * @var TwilioServiceInterface
     */
    private $service;

    /**
     * @var NumberVerifyTransformer
     */
    private $verifyTransformer;

    public function __construct(
        TwilioServiceInterface  $service,
        NumberVerifyTransformer $verifyTransformer
    ) {
        $this->service = $service;
        $this->verifyTransformer = $verifyTransformer;
    }

    /**
     * Verify Twilio SMS Response
     *
     * @param Request $request
     * @return type
     */
    public function verify(Request $request)
    {
        // Handle SMS Verification Request
        $request = new VerifySmsRequest($request->all());
        if ($request->validate()) {
            // Verify Twilio Number Response
            return $this->response->item($this->service->verify($request->all()), $this->verifyTransformer);
        }

        return $this->response->errorBadRequest();
    }
}

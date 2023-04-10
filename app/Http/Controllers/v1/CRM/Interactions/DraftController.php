<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Interactions\GetEmailDraftRequest;
use App\Http\Requests\CRM\Interactions\SaveEmailDraftRequest;
use App\Http\Requests\CRM\Interactions\GetReplyEmailDraftRequest;
use App\Services\CRM\User\DTOs\EmailDraft;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use App\Transformers\CRM\Interactions\InteractionTransformer;

class DraftController extends RestfulControllerV2
{
    protected $service;

    public function __construct(
        InteractionServiceInterface $service,
        InteractionTransformer $interactionTransformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['getEmailDraft', 'saveEmailDraft', 'sendEmailDraft', 'getReplyEmailDraft']);
        $this->middleware('setSalesPersonIdOnRequest')->only(['getEmailDraft', 'saveEmailDraft', 'sendEmailDraft', 'getReplyEmailDraft']);
        $this->middleware('setUserIdOnRequest')->only(['getEmailDraft', 'saveEmailDraft', 'sendEmailDraft', 'getReplyEmailDraft']);
        $this->service = $service;
        $this->interactionTransformer = $interactionTransformer;
    }

    public function getEmailDraft(int $leadId, Request $request)
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $request = new GetEmailDraftRequest($requestData);

        if ($request->validate()) {

            return $this->response->array([
                'data' => $this->service->getEmailDraft($request->all())->toArray()
            ]);
        }

        return $this->response->errorBadRequest();
    }

    public function saveEmailDraft(int $leadId, Request $request)
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $request = new SaveEmailDraftRequest($requestData);

        if ($request->validate()) {

            return $this->response->array([
                'data' => $this->service->saveEmailDraft($request->all())->toArray()
            ]);
        }

        return $this->response->errorBadRequest();
    }

    public function getReplyEmailDraft(int $leadId, int $id, Request $request)
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $requestData['interaction_id'] = $id;
        $request = new GetReplyEmailDraftRequest($requestData);

        if ($request->validate()) {

            return $this->response->array([
                'data' => $this->service->getEmailDraft($request->all())->toArray()
            ]);
        }

        return $this->response->errorBadRequest();
    }
}
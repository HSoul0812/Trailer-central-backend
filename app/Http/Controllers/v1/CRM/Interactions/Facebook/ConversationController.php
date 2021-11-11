<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Interactions\Facebook\ShowConversationRequest;
use App\Http\Requests\CRM\Interactions\Facebook\GetConversationsRequest;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Transformers\CRM\Interactions\Facebook\ConversationTransformer;
use Dingo\Api\Http\Request;

class ConversationController extends RestfulControllerV2
{
    /**
     * @var ConversationRepositoryInterface
     */
    protected $repository;

    /**
     * @var ConversationServiceInterface
     */
    protected $service;

    /**
     * @var ConversationTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param ConversationRepositoryInterface $repo
     * @param ConversationTransformer $transformer
     */
    public function __construct(
        ConversationRepositoryInterface $repo,
        ConversationTransformer $transformer
    ) {
        $this->repository = $repo;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * Get Conversations
     * 
     * @mode GET
     * @param Request $request
     * @param int $leadId
     * @return Response
     */
    public function index(Request $request, int $leadId) {
        // Get Request Data
        $requestData = $request->all();
        if(!empty($leadId)) {
            $requestData['lead_id'] = $leadId;
        }

        // Convert to Request
        $request = new GetConversationsRequest($requestData);

        if ($request->validate()) {
            return $this->response->collection($this->repository->getAll($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Get Conversation
     * 
     * @mode GET
     * @param Request $request
     * @return Response
     */
    public function show(Request $request) {
        // Convert to Request
        $request = new ShowConversationRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->repository->get($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}
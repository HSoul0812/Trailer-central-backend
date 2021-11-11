<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\ShowConversationsRequest;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Transformers\CRM\Interactions\Facebook\ConversationTransformer;
use Dingo\Api\Http\Request;

class ConversationController extends RestfulController
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
            return $this->response->item($this->conversation->get($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}
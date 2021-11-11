<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Interactions\Facebook\GetMessagesRequest;
use App\Http\Requests\CRM\Interactions\Facebook\SendMessageRequest;
use App\Repositories\CRM\Interactions\Facebook\MessageRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;
use App\Transformers\CRM\Interactions\Facebook\MessageTransformer;
use Dingo\Api\Http\Request;

class MessageController extends RestfulController
{
    /**
     * @var MessageRepositoryInterface
     */
    protected $repository;

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
     * @param MessageRepositoryInterface $repo
     * @param MessageServiceInterface $service
     * @param MessageTransformer $transformer
     */
    public function __construct(
        MessageRepositoryInterface $repo,
        MessageServiceInterface $service,
        MessageTransformer $transformer
    ) {
        $this->repository = $repo;
        $this->service = $service;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * Get Messages
     * 
     * @mode GET
     * @param Request $request
     * @param null|int $leadId
     * @return Response
     */
    public function index(Request $request, ?int $leadId = null) {
        // Get Request Data
        $requestData = $request->all();
        if(!empty($leadId)) {
            $requestData['lead_id'] = $leadId;
        }

        // Convert to Request
        $request = new GetMessagesRequest($requestData);

        if ($request->validate()) {
            return $this->response->paginate($this->repository->getAll($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
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
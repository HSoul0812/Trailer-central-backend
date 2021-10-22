<?php

namespace App\Http\Controllers\v1\Integration\Facebook;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Integration\Facebook\GetChatRequest;
use App\Http\Requests\Integration\Facebook\ShowChatRequest;
use App\Http\Requests\Integration\Facebook\CreateChatRequest;
use App\Http\Requests\Integration\Facebook\UpdateChatRequest;
use App\Http\Requests\Integration\Facebook\DeleteChatRequest;
use App\Repositories\Integration\Facebook\ChatRepositoryInterface;
use App\Services\Integration\Facebook\ChatServiceInterface;
use App\Transformers\Integration\Facebook\ChatTransformer;

class ChatController extends RestfulControllerV2 {
    /**
     * @var App\Repositories\Integration\Facebook\ChatRepositoryInterface
     */
    private $repository;

    /**
     * @var App\Services\Integration\Facebook\ChatServiceInterface
     */
    private $service;

    /**
     * @var App\Transformers\Integration\Facebook\ChatTransformer
     */
    private $transformer;

    public function __construct(
        ChatRepositoryInterface $repository,
        ChatServiceInterface $service,
        ChatTransformer $transformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
        $this->middleware('setUserIdOnRequest')->only(['create', 'update', 'index']);

        $this->repository = $repository;
        $this->service = $service;
        $this->transformer = $transformer;
    }

    /**
     * Get Facebook Chat Integrations With Access Tokens
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Facebook Chat Request
        $request = new GetChatRequest($request->all());
        if ($request->validate()) {
            // Get Chat Integrations
            return $this->response->paginator($this->repository->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Get Facebook Chat and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Facebook Chat Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowChatRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->show($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Facebook Chat and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Facebook Chat Request
        $request = new CreateChatRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->create($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Facebook Chat and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Facebook Chat Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateChatRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->update($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Delete Facebook Chat and Access Token
     * 
     * @param int $id
     * @return type
     */
    public function destroy(int $id)
    {
        // Handle Facebook Chat Request
        $request = new DeleteChatRequest(['id' => $id]);
        if ($request->validate() && $this->service->delete($id)) {
            return $this->successResponse();
        }
        
        return $this->response->errorBadRequest();
    }
}

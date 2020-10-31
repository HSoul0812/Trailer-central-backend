<?php

namespace App\Http\Controllers\v1\Integration\Auth;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Integration\Auth\GetFacebookRequest;
use App\Http\Requests\Integration\Auth\ShowFacebookRequest;
use App\Http\Requests\Integration\Auth\CreateFacebookRequest;
use App\Http\Requests\Integration\Auth\UpdateFacebookRequest;
use App\Http\Requests\Integration\Auth\PayloadFacebookRequest;
use App\Services\Integration\FacebookServiceInterface;

class FacebookController extends RestfulControllerV2 {
    /**
     * @var App\Services\Integration\FacebookServiceInterface
     */
    private $service;

    public function __construct(FacebookServiceInterface $service) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update']);

        $this->service = $service;
    }

    /**
     * Get Facebook Credentials and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowFacebookRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->show($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Facebook Credentials and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new CreateFacebookRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->create($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Facebook Credentials and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateFacebookRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->update($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Receive Facebook Payload
     * 
     * @param Request $request
     * @return type
     */
    public function payload(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new PayloadFacebookRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->update($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }
}

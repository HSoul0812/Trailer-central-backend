<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\User\ShowSalesAuthRequest;
use App\Http\Requests\CRM\User\CreateSalesAuthRequest;
use App\Http\Requests\CRM\User\UpdateSalesAuthRequest;
use App\Services\CRM\User\SalesAuthServiceInterface;

class SalesAuthController extends RestfulControllerV2 {
    /**
     * @var App\Services\CRM\User\SalesAuthServiceInterface
     */
    private $service;

    /**
     * @param SalesAuthServiceInterface $service
     */
    public function __construct(SalesAuthServiceInterface $service) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update']);
        $this->middleware('setUserIdOnRequest')->only(['create', 'update']);

        $this->service = $service;
    }

    /**
     * Get Sales Person and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowSalesAuthRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->show($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Sales Person and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new CreateSalesAuthRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->create($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Sales Person and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateSalesAuthRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->update($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Sales Person and Get Login URL
     * 
     * @param Request $request
     * @return type
     */
    public function login(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new LoginSalesAuthRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->login($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Authorize OAuth With Code
     * 
     * @param Request $request
     * @return type
     */
    public function authorize(Request $request)
    {
        // Handle Authorize Sales People Request
        $request = new AuthorizeSalesAuthRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->authorize($request->token_type, $request->auth_code, $request->state, $request->redirect_uri, $request->scopes));
        }
        
        return $this->response->errorBadRequest();
    }
}

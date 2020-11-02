<?php

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Integration\Auth\GetCatalogRequest;
use App\Http\Requests\Integration\Auth\ShowCatalogRequest;
use App\Http\Requests\Integration\Auth\CreateCatalogRequest;
use App\Http\Requests\Integration\Auth\UpdateCatalogRequest;
use App\Http\Requests\Integration\Auth\PayloadCatalogRequest;
use App\Services\Integration\Facebook\CatalogServiceInterface;
use App\Transformers\Integration\Facebook\CatalogTransformer;

class FacebookController extends RestfulControllerV2 {
    /**
     * @var App\Services\Integration\FacebookServiceInterface
     */
    private $service;

    public function __construct(CatalogServiceInterface $service) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update', 'index']);

        $this->service = $service;
    }

    /**
     * Get Facebook Catalogs With Access Tokens
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Facebook Catalog Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new GetCatalogRequest($requestData);
        if ($request->validate()) {
            // Get Catalogs
            return $this->response->paginator($this->catalogs->getAll($request->all()), new CatalogTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Get Facebook Catalog and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Facebook Catalog Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowCatalogRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->show($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Facebook Catalog and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Facebook Catalog Request
        $request = new CreateCatalogRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->create($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Facebook Catalog and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Facebook Catalog Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateCatalogRequest($requestData);
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
        // Handle Facebook Catalog Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new PayloadCatalogRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->update($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }
}

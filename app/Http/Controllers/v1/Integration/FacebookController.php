<?php

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Integration\Facebook\GetCatalogRequest;
use App\Http\Requests\Integration\Facebook\ShowCatalogRequest;
use App\Http\Requests\Integration\Facebook\CreateCatalogRequest;
use App\Http\Requests\Integration\Facebook\UpdateCatalogRequest;
use App\Http\Requests\Integration\Facebook\DeleteCatalogRequest;
use App\Http\Requests\Integration\Facebook\PayloadCatalogRequest;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Services\Integration\Facebook\CatalogServiceInterface;
use App\Transformers\Integration\Facebook\CatalogTransformer;

class FacebookController extends RestfulControllerV2 {
    /**
     * @var App\Services\Integration\CatalogServiceInterface
     */
    private $service;

    public function __construct(CatalogRepositoryInterface $catalogs, CatalogServiceInterface $service) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update', 'index']);

        $this->catalogs = $catalogs;
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
        $request = new GetCatalogRequest($request->all());
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
     * Delete Facebook Catalog and Access Token
     * 
     * @param int $id
     * @return type
     */
    public function destroy(int $id)
    {
        // Handle Facebook Catalog Request
        $request = new DeleteCatalogRequest(['id' => $id]);
        if ($request->validate() && $this->service->delete($id)) {
            return $this->successResponse();
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Receive Facebook Payload
     * 
     * @param Request $request
     * @return type
     */
    public function payload(Request $request)
    {
        // Handle Facebook Catalog Request
        $requestData = $request->all();
        $request = new PayloadCatalogRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->service->payload($request->payload));
        }
        
        return $this->response->errorBadRequest();
    }
}

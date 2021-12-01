<?php

namespace App\Http\Controllers\v1\Dispatch;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Dispatch\Facebook\GetMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\ShowMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\UpdateMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\DeleteMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\LoginMarketplaceRequest;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Services\Dispatch\Facebook\MarketplaceServiceInterface;
use App\Transformers\Marketing\Facebook\MarketplaceTransformer;
use App\Transformers\Marketing\Facebook\ListingTransformer;
use App\Transformers\Dispatch\Facebook\StatusTransformer;

class FacebookController extends RestfulControllerV2 {
    /**
     * @var App\Services\Marketing\MarketplaceRepositoryInterface
     */
    private $repository;

    /**
     * @var App\Services\Dispatch\MarketplaceServiceInterface
     */
    private $service;

    public function __construct(
        MarketplaceRepositoryInterface $repository,
        MarketplaceServiceInterface $service,
        MarketplaceTransformer $transformer,
        ListingTransformer $listingTransformer,
        StatusTransformer $statusTransformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update', 'index']);

        $this->repository = $repository;
        $this->service = $service;
        $this->transformer = $transformer;
        $this->statusTransformer = $statusTransformer;
    }

    /**
     * Get Facebook Marketplace Integrations
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Facebook Marketplace Request
        $request = new GetMarketplaceRequest($request->all());
        if ($request->validate()) {
            // Get Marketplaces
            return $this->response->item($this->service->status(), $this->statusTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Get Facebook Marketplace Integration
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Facebook Marketplace Request
        $requestData = $request->all();
        $requestData['marketplace_id'] = $id;
        $request = new ShowMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->repository->get($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Facebook Marketplace Integration
     * 
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function create(int $id, Request $request)
    {
        // Handle Facebook Marketplace Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new CreateMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->create($request), $this->listingTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Facebook Marketplace Integration
     * 
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Facebook Marketplace Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->update($request), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Delete Facebook Marketplace Integration
     * 
     * @param int $id
     * @return type
     */
    public function destroy(int $id)
    {
        // Handle Facebook Marketplace Request
        $request = new DeleteMarketplaceRequest(['id' => $id]);
        if ($request->validate() && $this->service->delete($id)) {
            return $this->successResponse();
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Login to Facebook Marketplace Integration
     * 
     * @param Request $request
     * @return type
     */
    public function login(Request $request)
    {
        // Handle Login Facebook Marketplace Request
        $request = new LoginMarketplaceRequest($request->all());
        if ($request->validate()) {
            // Get Acccess Token
            return $this->response->array([
                'data' => $this->service->login($request->client_uuid,
                                                $request->ip_address,
                                                $request->version)
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}

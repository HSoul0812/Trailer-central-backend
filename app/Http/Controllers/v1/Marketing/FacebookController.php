<?php

namespace App\Http\Controllers\v1\Marketing;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Marketing\Facebook\GetMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\ShowMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\UpdateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\DeleteMarketplaceRequest;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Services\Marketing\Facebook\MarketplaceServiceInterface;
use App\Transformers\Marketing\Facebook\MarketplaceTransformer;

class FacebookController extends RestfulControllerV2 {
    /**
     * @var App\Services\Marketing\MarketplaceRepositoryInterface
     */
    private $repository;

    /**
     * @var App\Services\Marketing\MarketplaceServiceInterface
     */
    private $service;

    public function __construct(
        MarketplaceRepositoryInterface $repository,
        MarketplaceServiceInterface $service,
        MarketplaceTransformer $transformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update', 'index']);

        $this->repository = $repository;
        $this->service = $service;
        $this->transformer = $transformer;
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
            return $this->response->paginator($this->repository->getAll($request->all()), $this->transformer);
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
        $requestData['id'] = $id;
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
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Facebook Marketplace Request
        $request = new CreateMarketplaceRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->create($request), $this->transformer);
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
}

<?php

namespace App\Http\Controllers\v1\Marketing\Facebook;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Marketing\Facebook\GetMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\ShowMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\UpdateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\DeleteMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\StatusMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\SmsMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\DismissErrorRequest;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\ErrorRepositoryInterface;
use App\Services\Marketing\Facebook\MarketplaceServiceInterface;
use App\Transformers\CRM\Text\NumberVerifyTransformer;
use App\Transformers\Marketing\Facebook\MarketplaceTransformer;
use App\Transformers\Marketing\Facebook\ErrorTransformer;
use App\Transformers\Marketing\Facebook\StatusTransformer;

class MarketplaceController extends RestfulControllerV2 {
    /**
     * @var App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface
     */
    private $repository;

    /**
     * @var App\Services\Marketing\Facebook\MarketplaceServiceInterface
     */
    private $service;

    /**
     * @var App\Transformers\Marketing\Facebook\MarketplaceTransformer
     */
    private $transformer;

    /**
     * @var App\Transformers\Marketing\Facebook\StatusTransformer
     */
    private $statusTransformer;

    /**
     * @var App\Transformers\Marketing\Facebook\ErrorTransformer
     */
    private $errorTransformer;

    /**
     * @var App\Transformers\CRM\Text\NumberVerifyTransformer
     */
    private $verifyTransformer;

    public function __construct(
        MarketplaceRepositoryInterface $repository,
        MarketplaceServiceInterface $service,
        ErrorRepositoryInterface $errors,
        MarketplaceTransformer $transformer,
        StatusTransformer $statusTransformer,
        ErrorTransformer $errorTransformer,
        NumberVerifyTransformer $verifyTransformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'update', 'index', 'status']);

        $this->repository = $repository;
        $this->service = $service;
        $this->errors = $errors;
        $this->transformer = $transformer;
        $this->statusTransformer = $statusTransformer;
        $this->errorTransformer = $errorTransformer;
        $this->verifyTransformer = $verifyTransformer;
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
            return $this->response->item($this->service->create($request->all()), $this->transformer);
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
            return $this->response->item($this->service->update($request->all()), $this->transformer);
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
     * Return Status for Marketplace
     * 
     * @param Request $request
     * @return type
     */
    public function status(Request $request)
    {
        // Handle Facebook Marketplace Status Request
        $requestData = $request->all();
        $request = new StatusMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->status($request->dealer_id), $this->statusTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Return SMS Number From Twilio
     * 
     * @param Request $request
     * @return type
     */
    public function sms(Request $request)
    {
        // Handle Facebook Marketplace Request
        $requestData = $request->all();
        $request = new SmsMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->sms($request->sms_number), $this->verifyTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Dismiss Error for Marketplace
     * 
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function dismiss(int $id, Request $request)
    {
        // Handle Dismiss Facebook Marketplace Error Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new DismissErrorRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->errors->dismiss($request->id, $request->error_id), $this->errorTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
}

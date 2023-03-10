<?php

namespace App\Http\Controllers\v1\Dispatch;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Dispatch\Facebook\GetMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\ShowMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\StepMarketplaceRequest;
use App\Http\Requests\Dispatch\Facebook\LoginMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\DeleteMarketplaceRequest;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStep;
use App\Services\Dispatch\Facebook\MarketplaceServiceInterface;
use App\Transformers\Marketing\Facebook\MarketplaceTransformer;
use App\Transformers\Marketing\Facebook\ListingTransformer;
use App\Transformers\Dispatch\Facebook\DealerTransformer;
use App\Transformers\Dispatch\Facebook\StatusTransformer;
use App\Transformers\Dispatch\Facebook\StepTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Dispatch\Facebook\UpdateMarketplaceMetricsRequest;
use App\Models\Marketing\Facebook\MarketplaceMetric;

class FacebookController extends RestfulControllerV2 {
    /**
     * @var MarketplaceRepositoryInterface
     */
    private $repository;

    /**
     * @var MarketplaceServiceInterface
     */
    private $service;

    /**
     * @var MarketplaceTransformer
     */
    private $transformer;

    /**
     * @var DealerTransformer
     */
    private $dealerTransformer;

    /**
     * @var ListingTransformer
     */
    private $listingTransformer;

    /**
     * @var StatusTransformer
     */
    private $statusTransformer;

    /**
     * @var StepTransformer
     */
    private $stepTransformer;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        MarketplaceRepositoryInterface $repository,
        MarketplaceServiceInterface $service,
        MarketplaceTransformer $transformer,
        DealerTransformer $dealerTransformer,
        ListingTransformer $listingTransformer,
        StatusTransformer $statusTransformer,
        StepTransformer $stepTransformer,
        Manager $fractal
    ) {
        $this->repository = $repository;
        $this->service = $service;

        $this->transformer = $transformer;
        $this->dealerTransformer = $dealerTransformer;
        $this->listingTransformer = $listingTransformer;
        $this->statusTransformer = $statusTransformer;
        $this->stepTransformer = $stepTransformer;

        // Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());
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
            return $this->response->item($this->service->status($request->all()), $this->statusTransformer);
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
        $startTime = microtime(true);
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Item Facebook Dispatch Dealer Transformer
            Log::channel('dispatch-fb')->info('Debug time after validating FB Inventory endpoint: ' . (microtime(true) - $startTime));
            $data = $this->itemResponse(
                $this->service->dealer($request->id, $request->all(), $startTime),
                $this->dealerTransformer,
                'data');
            Log::channel('dispatch-fb')->info('Debug time after calling service: ' . (microtime(true) - $startTime));
            return $data;
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Create Facebook Marketplace Inventory
     *
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function create(int $id, Request $request)
    {
        // Handle Facebook Marketplace Request
        $requestData = $request->all();
        $requestData['marketplace_id'] = $id;
        $request = new CreateMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->create($request->all()), $this->listingTransformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Log Step on Facebook Marketplace
     *
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Facebook Marketplace Request
        $requestData = $request->all();
        $requestData['marketplace_id'] = $id;
        $request = new StepMarketplaceRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->step(new MarketplaceStep($request->all())), $this->stepTransformer);
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

    public function metrics(int $id, Request $request)
    {
        $metricRequest = new UpdateMarketplaceMetricsRequest($request->all());
        if ($metricRequest->validate()) {
            MarketplaceMetric::updateOrCreate(
                [
                    'marketplace_id' => $id,
                    'category' => $request->category ?? '',
                    'name' => $request->name
                ],
                [
                    'value' => $request->value
                ]
            );
            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }
}

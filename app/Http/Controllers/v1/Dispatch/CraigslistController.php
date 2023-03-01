<?php

namespace App\Http\Controllers\v1\Dispatch;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Http\Requests\Dispatch\Craigslist\GetCraigslistRequest;
use App\Http\Requests\Dispatch\Craigslist\ShowCraigslistRequest;
use App\Http\Requests\Dispatch\Craigslist\LoginCraigslistRequest;
use App\Services\Dispatch\Craigslist\CraigslistServiceInterface;
use App\Transformers\Marketing\Craigslist\DealerTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;

class CraigslistController extends RestfulControllerV2 {
    /**
     * @var DealerTransformer
     */
    private $dealerTransformer;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        CraigslistServiceInterface $service,
        DealerTransformer $dealerTransformer,
        Manager $fractal
    ) {
        $this->service = $service;

        $this->dealerTransformer = $dealerTransformer;

        // Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Get Craigslist Integrations
     *
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Craigslist Request
        $request = new GetCraigslistRequest($request->all());
        if ($request->validate()) {
            // Get Craigslist Dealers
            return $this->response->collection($this->service->status($request->all()), $this->dealerTransformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Get Craigslist Integration
     *
     * @param Request $request
     * @return type
     */
    /*public function show(int $id, Request $request)
    {
        // Handle Craigslist Request
        $startTime = microtime(true);
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowCraigslistRequest($requestData);
        if ($request->validate()) {
            // Return Item Craigslist Dispatch Dealer Transformer
            Log::channel('dispatch-cl')->info('Debug time after validating FB Inventory endpoint: ' . (microtime(true) - $startTime));
            $data = $this->itemResponse(
                $this->service->dealer($request->id, $request->all(), $startTime),
                $this->dealerTransformer,
                'data');
            Log::channel('dispatch-cl')->info('Debug time after calling service: ' . (microtime(true) - $startTime));
            return $data;
        }

        return $this->response->errorBadRequest();
    }*/

    /**
     * Create Craigslist Inventory
     *
     * @param int $id
     * @param Request $request
     * @return type
     */
    /*public function create(int $id, Request $request)
    {
        // Handle Craigslist Request
        $requestData = $request->all();
        $requestData['marketplace_id'] = $id;
        $request = new CreateCraigslistRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->create($request->all()), $this->listingTransformer);
        }

        return $this->response->errorBadRequest();
    }*/

    /**
     * Log Step on Craigslist
     *
     * @param Request $request
     * @return type
     */
    /*public function update(int $id, Request $request)
    {
        // Handle Craigslist Request
        $requestData = $request->all();
        $requestData['marketplace_id'] = $id;
        $request = new StepCraigslistRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->service->step(new CraigslistStep($request->all())), $this->stepTransformer);
        }

        return $this->response->errorBadRequest();
    }*/

    /**
     * Login to Craigslist Integration
     *
     * @param Request $request
     * @return type
     */
    public function login(Request $request)
    {
        // Handle Login Craigslist Request
        $request = new LoginCraigslistRequest($request->all());
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

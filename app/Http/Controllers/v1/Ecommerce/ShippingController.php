<?php
namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Ecommerce\CalculateShippingCostsRequest;
use App\Services\Ecommerce\Shipping\ShippingServiceInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Http\JsonResponse;

class ShippingController extends RestfulController
{
    /** @var ShippingServiceInterface */
    private $shippingService;

    /**
     * ShippingController constructor.
     * @param ShippingServiceInterface $shippingService
     */
    public function __construct(ShippingServiceInterface $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    public function calculateCosts(Request $request): JsonResponse
    {
        $calculateRequest = new CalculateShippingCostsRequest($request->all());

        if (!$calculateRequest->validate()) {
            $this->response->errorBadRequest();
        }

        $cost = $this->shippingService->calculateShippingCosts($calculateRequest->all());

        return new JsonResponse($cost);
    }
}
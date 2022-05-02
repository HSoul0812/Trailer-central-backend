<?php

namespace App\Http\Controllers\v1\Subscription;

use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use Exception;
use Dingo\Api\Http\Request;
use App\Services\Subscription\StripeService;
use App\Services\Subscription\StripeServiceInterface;

use http\Env;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Subscription\CustomerTransformer;

use App\Http\Controllers\RestfulControllerV2;

/**
 * Class BulkUpdateController
 * @package App\Http\Controllers\v1\Manufacturer
 */
class SubscriptionController extends RestfulControllerV2
{

    /**
     * @var StripeService
     */
    protected $stripe;
    private $user;
    private $subscriptionRepository;


    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->subscriptionRepository = new SubscriptionRepository($this->user);

            return $next($request);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/list",
     *     description="Retrieves all subscriptions from auth user",
     *     tags={"Subscriptions"},
     *     @OA\Response(
     *         response="200",
     *         description="Retrieves all subscriptions from auth user",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getAll(Request $request) {
        return $this->response->array($this->subscriptionRepository->getAll());
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/customer",
     *     description="Retrieves a customer information like subscriptions and card",
     *     tags={"Subscriptions"},
     *     @OA\Response(
     *         response="200",
     *         description="Retrieves a customer information like subscriptions and card",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getCustomer(Request $request) {
        return $this->response->item($this->subscriptionRepository->getCustomer(), new CustomerTransformer());
    }
}

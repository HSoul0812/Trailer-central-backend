<?php

namespace App\Http\Controllers\v1\Subscription;

use App\Http\Requests\Subscriptions\GetCustomerByDealerIdRequest;
use App\Http\Requests\Subscriptions\GetExistingPlansRequest;
use App\Http\Requests\Subscriptions\SubscribeToPlanByDealerIdRequest;
use App\Http\Requests\Subscriptions\UpdateCardByDealerIdRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Services\Subscription\StripeService;

use Illuminate\Support\Facades\Auth;
use App\Transformers\Subscription\CustomerTransformer;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;

use App\Http\Controllers\RestfulControllerV2;

/**
 * Class SubscriptionController
 * @package App\Http\Controllers\v1\Subscription
 */
class SubscriptionController extends RestfulControllerV2
{

    /**
     * @var StripeService $stripe
     */
    protected $stripe;

    /**
     * @var SubscriptionRepositoryInterface $subscriptionRepository
     */
    private $subscriptionRepository;


    /**
     * Create a new controller instance.
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->middleware('validateDealerIdOnRequest');
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/customer",
     *     description="Retrieves a customer information like subscriptions and card",
     *     tags={"Subscriptions"},
     *     @OA\Parameter(
     *         name="transactions_limit",
     *         in="query",
     *         description="Transactions Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Retrieves a customer information like subscriptions and card",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getCustomerByDealerId(Request $request): Response {
        $request = new GetCustomerByDealerIdRequest(
            $request->all()
        );

        if ($request->validate()) {
            return $this->response->item(
                $this->subscriptionRepository->getCustomerByDealerId(
                    $request->dealer_id
                ),
                new CustomerTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/plans",
     *     description="Retrieves plans",
     *     tags={"Subscriptions"},
     *     @OA\Response(
     *         response="200",
     *         description="Retrieves plans",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getExistingPlans(Request $request): Response
    {
        $request = new GetExistingPlansRequest(
            $request->all()
        );

        if ($request->validate()) {
            return $this->response->array(
                $this->subscriptionRepository->getExistingPlans()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/subscribe",
     *     description="Subscribe to a selected plan",
     *     tags={"Subscriptions"},
     *     @OA\Response(
     *         response="200",
     *         description="Retrieves plans",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function subscribeToPlanByDealerId(Request $request): Response
    {
        $request = new SubscribeToPlanByDealerIdRequest(
            $request->all()
        );

        if ($request->validate() &&
            $this->subscriptionRepository->subscribeToPlanByDealerId(
                $request->dealer_id,
                $request->plan
            )
        ) {
            return $this->response->array([
                'response' => [
                    'status' => 'success'
                ]
            ]);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/update-card",
     *     description="Updates a customer card",
     *     tags={"Subscriptions"},
     *     @OA\Response(
     *         response="200",
     *         description="Updates a customer card",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function updateCardByDealerId(Request $request): Response
    {
        $request = new UpdateCardByDealerIdRequest(
            $request->all()
        );

        if ($request->validate() &&
           $this->response->array(
                $this->subscriptionRepository->updateCardByDealerId(
                    $request->dealer_id,
                    $request->token
                )->toArray()
            )
        ) {
            return $this->response->array([
                'response' => [
                    'status' => 'success'
                ]
            ]);
        }

        return $this->response->errorBadRequest();
    }
}

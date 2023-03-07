<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Marketing\Craigslist\GetBillingRequest;
use App\Repositories\Marketing\Craigslist\BillingRepositoryInterface;
use App\Transformers\Marketing\Craigslist\BillingTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class BillingController extends RestfulControllerV2
{
    /**
     * @var BillingRepositoryInterface
     */
    protected $repository;

    /**
     * @var BillingTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance
     */
    public function __construct(
        BillingRepositoryInterface $billingRepository,
        BillingTransformer $billingTransformer
    ) {
        $this->repository = $billingRepository;
        $this->transformer = $billingTransformer;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }


    /**
     * Get Scheduler Calendar Range
     *
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request)
    {
        $request = new GetBillingRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->repository->calendar($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }
}

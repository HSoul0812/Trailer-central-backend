<?php

namespace App\Http\Controllers\v1\User;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Http\Controllers\RestfulController;
use App\Http\Requests\User\GetDealerLocationQuoteFeeRequest;
use App\Repositories\User\DealerLocationQuoteFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Transformers\User\DealerLocationQuoteFeeTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Http\Requests\User\GetDealerLocationRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DealerLocationController extends RestfulController {

    /**
     * @var DealerLocationQuoteFeeRepositoryInterface
     */
    protected $dealerLocationQuoteFee;

    protected $dealerLocation;

    protected $transformer;

    public function __construct(
        DealerLocationRepositoryInterface $dealerLocationRepo,
        DealerLocationQuoteFeeRepositoryInterface $dealerLocationRepoFee
    )
    {
        $this->middleware('setDealerIdOnRequest')->only(['index','quoteFees']);
        $this->dealerLocation = $dealerLocationRepo;
        $this->dealerLocationQuoteFee = $dealerLocationRepoFee;
        $this->transformer = new DealerLocationTransformer();
    }

    public function index(Request $request) {
        $request = new GetDealerLocationRequest($request->all());
        if ($request->validate()) {
            return $this->response->paginator($this->dealerLocation->getAll($request->all()), new DealerLocationTransformer);
        }
        return $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response|void when there is a bad request it will throw an HttpException and request life cycle ends
     *
     * @throws NoObjectIdValueSetException
     * @throws HttpException when there is a bad request
     */
    public function quoteFees(Request $request): Response
    {
        $request = new GetDealerLocationQuoteFeeRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->dealerLocationQuoteFee->getAll($request->all()), new DealerLocationQuoteFeeTransformer());
        }

        $this->response->errorBadRequest();
    }
}

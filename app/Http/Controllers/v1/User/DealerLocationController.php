<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\GetDealerLocationQuoteFeeRequest;
use App\Http\Requests\User\GetDealerLocationRequest;
use App\Http\Requests\User\SaveDealerLocationRequest;
use App\Http\Requests\User\UpdateDealerLocationRequest;
use App\Repositories\User\DealerLocationQuoteFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Transformers\User\DealerLocationQuoteFeeTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Http\Requests\User\CommonDealerLocationRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Fractal\Manager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use League\Fractal\Resource\Item;

class DealerLocationController extends RestfulControllerV2 {

    /**
     * @var DealerLocationQuoteFeeRepositoryInterface
     */
    protected $dealerLocationQuoteFee;

    /** @var DealerLocationRepositoryInterface */
    protected $dealerLocation;

    /** @var DealerLocationTransformer */
    protected $transformer;

    /** @var Manager */
    private $fractal;

    public function __construct(
        DealerLocationRepositoryInterface $dealerLocationRepo,
        DealerLocationQuoteFeeRepositoryInterface $dealerLocationRepoFee,
        Manager $fractal
    )
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'quoteFees', 'destroy', 'update', 'show', 'create', 'update'
        ]);
        $this->dealerLocation = $dealerLocationRepo;
        $this->dealerLocationQuoteFee = $dealerLocationRepoFee;
        $this->transformer = new DealerLocationTransformer();
        $this->fractal = $fractal;
    }

    /**
     * @return Response|void
     * @throws ResourceException when there was a failed validation
     */
    public function index(Request $request): Response
    {
        $request = new GetDealerLocationRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->dealerLocation->getAll($request->all()), $this->transformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @return Response|void
     *
     * @throws ModelNotFoundException
     * @throws ResourceException when there was a failed validation
     * @throws HttpException when the provided resource id does not belongs to dealer who has made the request
     */
    public function destroy(int $id, Request $request): Response
    {
        $request = new CommonDealerLocationRequest(['id' => $id, 'dealer_id' => $request->get('dealer_id')]);

        if ($request->validate()) {
            if ($this->dealerLocation->delete(['dealer_location_id' => $id])) {
                return $this->response->noContent();
            }

            $this->response->errorInternal();
        }

        $this->response->errorBadRequest();
    }

    /**
     * @return Response|void
     *
     * @throws ModelNotFoundException
     * @throws ResourceException when there was a failed validation
     * @throws HttpException when the provided resource id does not belongs to dealer who has made the request
     */
    public function show(int $id, Request $request): Response
    {
        $request = new CommonDealerLocationRequest(array_merge(['id' => $id, 'dealer_id'], $request->all()));

        if ($request->validate()) {
            return $this->sendResponseForSingleLocationResponse($id, $request->include);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @return Response|void
     *
     * @throws ModelNotFoundException
     * @throws ResourceException when there was a failed validation
     * @throws HttpException when the provided resource id does not belongs to dealer who has made the request
     */
    public function create(Request $request): Response
    {
        $request = new SaveDealerLocationRequest($request->all());
//dd($request->all());
        if ($request->validate()) {
            $location = $this->dealerLocation->create($request->all());

            return $this->sendResponseForSingleLocationResponse($location->dealer_location_id, $request->include);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @return Response|void
     *
     * @throws ModelNotFoundException
     * @throws ResourceException when there was a failed validation
     * @throws HttpException when the provided resource id does not belongs to dealer who has made the request
     */
    public function update(Request $request): Response
    {
        $request = new UpdateDealerLocationRequest($request->all());

        if ($request->validate() && $this->dealerLocation->update($request->all())) {
            return $this->sendResponseForSingleLocationResponse($request->id, $request->include);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @return Response|void
     *
     * @throws ResourceException when there was a failed validation
     */
    public function quoteFees(Request $request): Response
    {
        $request = new GetDealerLocationQuoteFeeRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->dealerLocationQuoteFee->getAll($request->all()), new DealerLocationQuoteFeeTransformer());
        }

        $this->response->errorBadRequest();
    }

    private function sendResponseForSingleLocationResponse(int $id, string $include): Response
    {
        $this->fractal->parseIncludes($include);

        $locationItem = new Item($this->dealerLocation->get(['dealer_location_id' => $id]), $this->transformer);

        return $this->response->array($this->fractal->createData($locationItem)->toArray());
    }
}

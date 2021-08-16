<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\CheckDealerLocationRequest;
use App\Http\Requests\User\DeleteDealerLocationRequest;
use App\Http\Requests\User\GetDealerLocationQuoteFeeRequest;
use App\Http\Requests\User\GetDealerLocationRequest;
use App\Http\Requests\User\SaveDealerLocationRequest;
use App\Http\Requests\User\UpdateDealerLocationRequest;
use App\Repositories\User\DealerLocationQuoteFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\User\DealerLocationServiceInterface;
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

    /** @var DealerLocationServiceInterface */
    protected $service;

    /** @var DealerLocationTransformer */
    protected $transformer;

    /** @var Manager */
    private $fractal;

    public function __construct(
        DealerLocationServiceInterface $service,
        DealerLocationRepositoryInterface $dealerLocationRepo,
        DealerLocationQuoteFeeRepositoryInterface $dealerLocationRepoFee,
        Manager $fractal
    )
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'quoteFees', 'destroy', 'update', 'show', 'create', 'update', 'check'
        ]);
        $this->dealerLocation = $dealerLocationRepo;
        $this->dealerLocationQuoteFee = $dealerLocationRepoFee;
        $this->service = $service;
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
     * @param  Request  $request
     * @return Response
     */
    public function availableTaxCategories(Request $request): Response
    {
        return $this->response->array([
            'data' => collect($this->service::AVAILABLE_TAX_CATEGORIES)->map(function (string $name, int $id) {
                return ['id' => $id, 'name' => $name];
            })->values()
        ]);
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
        $request = new DeleteDealerLocationRequest(['id' => $id] + $request->all());

        if ($request->validate()) {
            if ($this->service->moveAndDelete($request->getId(), $request->getMoveReferencesToLocationId())) {
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
        $request = new CommonDealerLocationRequest(['id' => $id] + $request->all());

        if ($request->validate()) {
            return $this->sendResponseForSingleLocation($id, $request->getInclude());
        }

        $this->response->errorBadRequest();
    }

    /**
     * @return Response|void
     *
     * @throws ResourceException when there was a failed validation
     */
    public function check(string $name, Request $request): Response
    {
        $request = new CheckDealerLocationRequest($request->all() + ['name' => $name]);

        if ($request->validate()) {
            $exists = $this->dealerLocation->existByName(
                $request->getName(),
                $request->getDealerId(),
                $request->getId()
            );

            return $this->existsResponse($exists);
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

        if ($request->validate()) {
            $location = $this->service->create($request->getDealerId(), $request->all());

            return $this->sendResponseForSingleLocation($location->dealer_location_id, $request->getInclude());
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
    public function update(int $id, Request $request): Response
    {
        $request = new UpdateDealerLocationRequest(['id' => $id] + $request->all());

        if ($request->validate() && $this->service->update($request->getId(), $request->getDealerId(), $request->all())) {
            return $this->sendResponseForSingleLocation($request->getId(), $request->getInclude());
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

    private function sendResponseForSingleLocation(int $id, string $include): Response
    {
        $this->fractal->parseIncludes($include);

        $locationItem = new Item($this->dealerLocation->get(['dealer_location_id' => $id]), $this->transformer);

        return $this->response->array($this->fractal->createData($locationItem)->toArray());
    }
}

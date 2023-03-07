<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Integration\DeleteDealerIntegrationRequest;
use App\Http\Requests\Integration\UpdateDealerIntegrationRequest;
use App\Http\Requests\Integration\UpdateIntegrationRequest;
use App\Http\Requests\User\Integration\GetAllDealerIntegrationRequest;
use App\Http\Requests\User\Integration\GetSingleDealerIntegrationRequest;
use App\Repositories\User\Integration\DealerIntegrationRepository;
use App\Repositories\User\Integration\DealerIntegrationRepositoryInterface;
use App\Transformers\User\Integration\DealerIntegrationTransformer;
use App\Services\User\DealerIntegrationServiceInterface;
use Illuminate\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class DealerIntegrationController
 * @package App\Http\Controllers\v1\User
 */
class DealerIntegrationController extends RestfulControllerV2
{
    /**
     * @var DealerIntegrationServiceInterface
     */
    protected $service;

    /**
     * @var DealerIntegrationTransformer
     */
    private $transformer;

    /**
     * @var DealerIntegrationRepositoryInterface
     */
    private $repository;

    /**
     * @param DealerIntegrationRepository $repository
     * @param DealerIntegrationServiceInterface $service
     * @param DealerIntegrationTransformer $transformer
     */
    public function __construct(
        DealerIntegrationRepositoryInterface $repository,
        DealerIntegrationServiceInterface $service,
        DealerIntegrationTransformer $transformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index','show','update','delete']);
        $this->repository = $repository;
        $this->service = $service;
        $this->transformer = $transformer;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function index(Request $request): Response
    {
        $integrationRequest = new GetAllDealerIntegrationRequest($request->all());

        if ($integrationRequest->validate()) {
            return $this->response->collection(
                $this->repository->getAll([
                    'dealer_id' => $integrationRequest->dealer_id
                ]),
                $this->transformer
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $id the integration id
     * @param Request $request
     * @return Response
     *
     * @throws \Dingo\Api\Exception\ResourceException when there was a failed validation
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     */
    public function show(int $id, Request $request): Response
    {
        $integrationRequest = new GetSingleDealerIntegrationRequest($request->all() + ['integration_id' => $id]);

        if ($integrationRequest->validate()) {
            return $this->response->item(
                $this->repository->get([
                    'integration_id' => $integrationRequest->integration_id,
                    'dealer_id' => $integrationRequest->dealer_id
                ]),
                $this->transformer
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function update(int $id, Request $request): Response
    {
        $integrationRequest = new UpdateDealerIntegrationRequest($request->all() + ['integration_id' => $id]);

        if ($integrationRequest->validate()) {
            $this->service->update([
                'integration_id' => $integrationRequest->integration_id,
                'dealer_id' => $integrationRequest->dealer_id,
                'settings' => $integrationRequest->settings,
                'active' => $integrationRequest->active,
                'location_ids' => $integrationRequest->location_ids
            ]);

            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function delete(int $id, Request $request): Response
    {
        $integrationRequest = new DeleteDealerIntegrationRequest($request->all() + ['integration_id' => $id]);

        if ($integrationRequest->validate()) {
            $this->service->delete([
                'integration_id' => $integrationRequest->integration_id,
                'dealer_id' => $integrationRequest->dealer_id,
                'active' => 0
            ]);

            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }
}

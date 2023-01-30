<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Integration\GetAllIntegrationRequest;
use App\Http\Requests\Integration\GetSingleIntegrationRequest;
use App\Repositories\Integration\IntegrationRepositoryInterface;
use App\Transformers\Integration\IntegrationTransformer;
use Illuminate\Http\Request;
use Dingo\Api\Http\Response;

class IntegrationController extends RestfulControllerV2
{
    /**
     * @var IntegrationRepositoryInterface
     */
    protected $repository;

    private $transformer;

    public function __construct(
        IntegrationRepositoryInterface $repository,
        IntegrationTransformer $transformer
    )
    {
        $this->middleware('setDealerIdOnRequest')->only(['index','show']);
        $this->repository = $repository;
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
        $integrationRequest = new GetAllIntegrationRequest($request->all());

        if ($integrationRequest->validate()) {
            return $this->response->collection(
                $this->repository->getAll([
                    'integrated' => $integrationRequest->integrated,
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
        $integrationRequest = new GetSingleIntegrationRequest($request->all() + ['integration_id' => $id]);

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
}

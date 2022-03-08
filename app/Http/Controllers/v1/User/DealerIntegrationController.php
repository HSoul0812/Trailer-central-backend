<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\Integration\GetSingleDealerIntegrationRequest;
use App\Repositories\User\Integration\DealerIntegrationRepositoryInterface;
use App\Transformers\User\Integration\DealerIntegrationTransformer;
use Illuminate\Http\Request;
use Dingo\Api\Http\Response;

class DealerIntegrationController extends RestfulControllerV2
{
    /**
     * @var DealerIntegrationRepositoryInterface
     */
    protected $repository;

    public function __construct(DealerIntegrationRepositoryInterface $repository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['show']);
        $this->repository = $repository;
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
                app()->make(DealerIntegrationTransformer::class)
            );
        }

        $this->response->errorBadRequest();
    }
}
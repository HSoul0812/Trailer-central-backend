<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Website\Config;

use App\Http\Requests\Website\Config\GetExtraWebsiteConfigRequest;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\Config\PutExtraWebsiteConfigRequest;
use App\Services\Website\ExtraWebsiteConfigServiceInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class ExtraWebsiteConfigController extends RestfulControllerV2
{
    /** @var ExtraWebsiteConfigServiceInterface */
    private $service;

    public function __construct(ExtraWebsiteConfigServiceInterface $service)
    {
        $this->service = $service;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'createOrUpdate']);
    }

    /**
     * @param int $websiteId
     * @return Response|void
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     */
    public function index(int $websiteId, Request $request): Response
    {
        $request = new GetExtraWebsiteConfigRequest(array_merge(['website_id' => $websiteId], $request->all()));

        if ($request->validate()) {
            return $this->response->array([
                'data' => $this->service->getAllByWebsiteId($request->website_id)
            ]);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $websiteId
     * @return Response|void
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     * @throws \Exception when something goes wrong at saving time
     */
    public function createOrUpdate(int $websiteId, Request $request): Response
    {
        $request = new PutExtraWebsiteConfigRequest(array_merge(['website_id' => $websiteId], $request->all()));

        if ($request->validate()) {
            $this->service->updateByWebsiteId($request->website_id, $request->all());

            return $this->updatedResponse();
        }

        $this->response->errorBadRequest();
    }
}

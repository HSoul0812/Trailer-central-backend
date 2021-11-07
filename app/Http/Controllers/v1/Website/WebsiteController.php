<?php

namespace App\Http\Controllers\v1\Website;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\EnableProxiedDomainSslRequest;
use App\Http\Requests\Website\GetAllRequest;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Services\Website\WebsiteService;
use App\Transformers\Website\WebsiteTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class WebsiteController
 * @package App\Http\Controllers\v1\Website
 */
class WebsiteController extends RestfulControllerV2
{
    /**
     * @var WebsiteService
     */
    private $websiteService;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * WebsiteController constructor.
     * @param WebsiteService $websiteService
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(WebsiteService $websiteService, WebsiteRepositoryInterface $websiteRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'enableProxiedDomainSsl']);

        $this->websiteService = $websiteService;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/website",
     *     description="Retrieve a list of websites",
     *     tags={"Website"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Per page",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Website Type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of websites",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response|void
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request): Response
    {
        $request = new GetAllRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $data = $this->websiteRepository->getAll($request->all());

        return $this->response->paginator($data, new WebsiteTransformer());
    }

    /**
     * @param int $websiteId
     * @return \Dingo\Api\Http\Response|void
     *
     * @OA\Post(
     *     path="/api/website/{websiteId}/enable-proxied-domain-ssl",
     *     description="Enable proxied domain for ssl",
     *     tags={"Website"},
     *     @OA\Parameter(
     *         name="websiteId",
     *         in="path",
     *         description="Website ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms proxied domain for ssl was enabled",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function enableProxiedDomainSsl(int $websiteId)
    {
        $request = new EnableProxiedDomainSslRequest(['website_id' => $websiteId]);

        if ($request->validate() && $this->websiteService->enableProxiedDomainSsl($websiteId)) {
            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }
}

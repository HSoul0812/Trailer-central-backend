<?php

namespace App\Http\Controllers\v1\Website;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Website\EnableProxiedDomainSslRequest;
use App\Services\Website\WebsiteService;

/**
 * Class WebsiteController
 * @package App\Http\Controllers\v1\Website
 */
class WebsiteController extends RestfulController
{
    /**
     * @var WebsiteService
     */
    private $websiteService;

    /**
     * WebsiteController constructor.
     * @param WebsiteService $websiteService
     */
    public function __construct(WebsiteService $websiteService)
    {
        $this->middleware('setDealerIdOnRequest')->only(['enableProxiedDomainSsl']);

        $this->websiteService = $websiteService;
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

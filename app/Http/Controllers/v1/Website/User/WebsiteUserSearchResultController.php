<?php

namespace App\Http\Controllers\v1\Website\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\User\CreateSearchResultRequest;
use App\Services\Website\WebsiteUserService;
use App\Transformers\Website\WebsiteUserSearchResultTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class WebsiteUserSearchResultController extends RestfulControllerV2
{
    /**
     * @var WebsiteUserService
     */
    private $websiteUserService;

    /**
     * @param WebsiteUserService $websiteUserService
     */
    public function __construct(WebsiteUserService $websiteUserService)
    {
        $this->websiteUserService = $websiteUserService;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function create(Request $request): Response
    {
        $dealerId = $this->user->website->dealer->getKey();

        $request = new CreateSearchResultRequest(
            array_merge($request->all(), ['dealer_id' => $dealerId, 'website_user_id' => $this->user->getKey()])
        );

        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $result = $this->websiteUserService->addSearchUrlToUser($request->all());

        return $this->response->item($result, new WebsiteUserSearchResultTransformer());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $websiteUserId = $this->user->getKey();

        $requestData = array_merge($request->all(), ['website_user_id' => $websiteUserId]);

        $lastSearchResults = $this->websiteUserService->getUserSearchResults($requestData);

        return $this->response->collection($lastSearchResults, new WebsiteUserSearchResultTransformer());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $websiteUserId = $this->user->getKey();

        $requestData = array_merge($request->all(), ['website_user_id' => $websiteUserId]);

        $this->websiteUserService->removeSearchResult($requestData);

        return $this->response->noContent();
    }
}

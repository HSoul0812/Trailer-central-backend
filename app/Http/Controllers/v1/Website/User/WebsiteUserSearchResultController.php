<?php

namespace App\Http\Controllers\v1\Website\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\User\CreateSearchResultRequest;
use App\Services\Website\WebsiteUserService;
use App\Transformers\Website\WebsiteUserSearchResultTransformer;
use Dingo\Api\Http\Request;

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

    public function create(Request $request)
    {
        $dealerId = $this->user->website->dealer->getKey();

        $websiteUserId = $this->user->getKey();
        $requestData = $request->all();

        $request = new CreateSearchResultRequest(
            array_merge($request->all(), ['dealer_id' => $dealerId])
        );

        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $result = $this->websiteUserService->addSearchUrlToUser(
            $requestData['search_url'],
            $websiteUserId
        );

        return $this->response->item($result, new WebsiteUserSearchResultTransformer());
    }

    public function index(Request $request)
    {
        $websiteUserId = $this->user->getKey();

        $requestData = array_merge($request->all(), ['website_user_id' => $websiteUserId]);

        $lastSearchResults = $this->websiteUserService->getUserSearchResults($requestData);

        return $this->response->collection($lastSearchResults, new WebsiteUserSearchResultTransformer());
    }
}

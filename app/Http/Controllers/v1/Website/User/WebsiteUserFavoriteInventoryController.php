<?php

namespace App\Http\Controllers\v1\Website\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\User\CreateFavoriteInventoryRequest;
use App\Http\Requests\Website\User\FavoriteInventoryRequest;
use App\Services\Website\WebsiteUserService;
use App\Services\Website\WebsiteUserServiceInterface;
use App\Transformers\Website\WebsiteUserFavoriteInventoryTransformer;
use Dingo\Api\Http\Request;

/**
 * Defines API endpoints for WebsiteUserFavoriteInventory resource.
 * Class WebsiteUserFavoriteInventoryController
 * @package App\Http\Controllers\v1\Website\User
 */
class WebsiteUserFavoriteInventoryController extends RestfulControllerV2
{
    /**
     * @var WebsiteUserService
     */
    private $websiteUserService;

    /**
     * @var WebsiteUserFavoriteInventoryTransformer
     */
    private $websiteUserFavoriteInventoryTransformer;

    /**
     * WebsiteUserFavoriteInventoryController constructor.
     * @param WebsiteUserServiceInterface $websiteUserService
     */
    public function __construct(WebsiteUserServiceInterface $websiteUserService) {
        $this->websiteUserService = $websiteUserService;
        $this->websiteUserFavoriteInventoryTransformer = new WebsiteUserFavoriteInventoryTransformer();
    }
    /**
     * @param Request $request
     */
    public function index(Request $request) {
        $websiteUserId = $this->user->getKey();
        $inventories = $this->websiteUserService->getUserInventories($websiteUserId);
        return $this->response->collection($inventories, $this->websiteUserFavoriteInventoryTransformer);
    }

    /**
     * @param Request $request
     */
    public function create(Request $request) {
        $dealerId = $this->user->website->dealer->getKey();

        $websiteUserId = $this->user->getKey();
        $requestData = $request->all();
        $request = new CreateFavoriteInventoryRequest(
            array_merge($request->all(), ['dealer_id' => $dealerId])
        );
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }
        $inventories = $this->websiteUserService->addUserInventories(
            $websiteUserId,
            $requestData['inventory_ids']
        );
        return $this->response->collection(collect($inventories), $this->websiteUserFavoriteInventoryTransformer);
    }

    /**
     * @param Request $request
     */
    public function delete(Request $request) {
        $websiteUserId = $this->user->getKey();
        $requestData = $request->all();
        $request = new FavoriteInventoryRequest($requestData);
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $this->websiteUserService->removeUserInventories($websiteUserId, $requestData['inventory_ids']);
        return $this->successResponse();
    }
}

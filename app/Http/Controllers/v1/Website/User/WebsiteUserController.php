<?php
namespace App\Http\Controllers\v1\Website\User;

use App\Http\Controllers\RestfulControllerV2;

use App\Http\Requests\Website\CreateUserRequest;
use App\Http\Requests\Website\GetAllRequest;
use App\Services\Website\WebsiteUserService;
use App\Services\Website\WebsiteUserServiceInterface;

use Dingo\Api\Http\Response;
use Illuminate\Http\Request;

class WebsiteUserController extends RestfulControllerV2 {
    /**
     * @var WebsiteUserServiceInterface $websiteUserService
     */
    private $websiteUserService;

    public function __construct(WebsiteUserServiceInterface $websiteUserService) {
        $this->websiteUserService = $websiteUserService;
    }

    /**
     * @param int $websiteId
     * @param Request $request
     */
    public function create(int $websiteId, Request $request): Response {
        $request = new CreateUserRequest($request->all());
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $this->websiteUserService->createUser($websiteId, $request->validated());
    }

}

<?php
namespace App\Http\Controllers\v1\Website\User;

use App\Http\Controllers\RestfulControllerV2;

use App\Http\Requests\Website\CreateUserRequest;
use App\Http\Requests\Website\GetAllRequest;
use App\Http\Requests\Website\LoginUserRequest;
use App\Services\Website\WebsiteUserService;
use App\Services\Website\WebsiteUserServiceInterface;

use App\Transformers\Website\WebsiteUserTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\QueryException;

class WebsiteUserController extends RestfulControllerV2 {
    /**
     * @var WebsiteUserServiceInterface $websiteUserService
     */
    private $websiteUserService;

    /**
     * @var WebsiteUserTransformer
     */
    private $userTransformer;

    public function __construct(WebsiteUserServiceInterface $websiteUserService) {
        $this->websiteUserService = $websiteUserService;
        $this->userTransformer = new WebsiteUserTransformer();
    }

    /**
     * @param int $websiteId
     * @param Request $request
     */
    public function create(int $websiteId, Request $request): Response {
        $requestData = array_replace($request->all(), ['website_id' => $websiteId]);
        $request = new CreateUserRequest($requestData);
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }
        try {
            $user = $this->websiteUserService->createUser($requestData);
            return $this->response->item($user, $this->userTransformer);
        } catch(QueryException $exception)  {
            $this->response->errorInternal();
        }
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response
     */
    public function login(int $websiteId, Request $request): Response {
        $requestData = array_replace($request->all(), ['website_id' => $websiteId]);
        $request = new LoginUserRequest($requestData);
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $user = $this->websiteUserService->loginUser($requestData);
        return $this->response->item($user, $this->userTransformer);
    }
}

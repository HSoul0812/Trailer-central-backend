<?php
namespace App\Http\Controllers\v1\Website\User;

use App\Http\Controllers\RestfulControllerV2;

use App\Http\Requests\Website\User\CreateRequest;
use App\Http\Requests\Website\User\LoginRequest;
use App\Http\Requests\Website\User\UpdateRequest;
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
        $request = new CreateRequest($requestData);
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
        $request = new LoginRequest($requestData);
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $user = $this->websiteUserService->loginUser($requestData);
        return $this->response->item($user, $this->userTransformer);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response {
        $user = $this->user;
        $requestData = $request->all();
        $request = new UpdateRequest($requestData);
        if(!$request->validate()) {
            $this->response->errorBadRequest();
        }

        if(isset($requestData['password']) && $requestData['password']) {
            $user->password = $requestData['password'];
        }

        unset($requestData['password']);

        $user->fill($requestData);
        $user->save();
        return $this->response->item($user, $this->userTransformer);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function get(Request  $request): Response {
        return $this->response->item($this->user, $this->userTransformer);
    }
}

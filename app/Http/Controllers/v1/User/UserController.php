<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Repositories\User\UserRepositoryInterface;
use App\Transformers\User\UserTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;


class UserController extends RestfulControllerV2
{

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request): Response {
        $getRequest = new GetUserRequest($request->all());
        if($getRequest->validate()) {
            $this->response->item(
                $this->userRepository->getByEmail($request->email),
                new UserTransformer()
            );
        }

        $this->response->errorBadRequest();
    }

    public function create(Request $request): Response {
        $createRequest = new CreateUserRequest($request->all());
        if($createRequest->validate()) {
            return $this->response->item(
                $this->userRepository->create($createRequest->all()),
                new UserTransformer()
            );
        }

        $this->response->errorBadRequest();
    }
}

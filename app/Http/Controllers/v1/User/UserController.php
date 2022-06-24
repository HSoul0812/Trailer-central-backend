<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
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

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * Get dealer by email
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function index(Request $request): Response {
        $getRequest = new GetUserRequest($request->all());
        if($getRequest->validate()) {
            return $this->response->item(
                $this->userRepository->getByEmail($request->email),
                new UserTransformer()
            );
        }

        $this->response->errorBadRequest();
    }

    /**
     * Create dealer
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function create(Request $request): Response {
        $createRequest = new CreateUserRequest($request->all());
        if($createRequest->validate()) {
            $user = $this->userRepository->create($createRequest->all());
            $user->clsf_active = 1;
            $user->save();
            return $this->response->item(
                $user,
                new UserTransformer()
            )->setStatusCode(201);
        }

        $this->response->errorBadRequest();
    }
}

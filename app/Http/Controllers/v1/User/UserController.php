<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\GetDealerRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Transformers\User\DealerClassifiedTransformer;
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
        $this->middleware('setDealerIdOnRequest');
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

        return $this->response->errorBadRequest();
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
            return $this->response->item(
                $this->userRepository->create($createRequest->all()),
                new UserTransformer()
            )->setStatusCode(201);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Activate Dealer Classifieds
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function activateDealerClassifieds(Request $request): Response {
        $getRequest = new GetDealerRequest($request->all());
        if($getRequest->validate()) {
            return $this->response->item(
                $this->userRepository->activateDealerClassifieds($request->dealer_id),
                new UserTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Deactivate Dealer Classifieds
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function deactivateDealerClassifieds(Request $request): Response {
        $getRequest = new GetDealerRequest($request->all());
        if($getRequest->validate()) {
            return $this->response->item(
                $this->userRepository->deactivateDealerClassifieds($request->dealer_id),
                new UserTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Deactivate Dealer Classifieds
     * @param Request $request
     * @return Response
     */
    public function getClassified(Request $request): Response {
        return $this->response->item(
            User::findOrFail($request->dealer_id),
            new DealerClassifiedTransformer()
        );
    }
}

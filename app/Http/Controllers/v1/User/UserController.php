<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DealerClassifiedsRequest;
use App\Http\Requests\User\GetDealerRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\DealerOptionsService;
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
     * @var DealerOptionsService
     */
    private $dealerOptionsService;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param DealerOptionsService $dealerOptionsService
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        DealerOptionsService $dealerOptionsService
    ) {
        $this->middleware('setDealerIdOnRequest');
        $this->userRepository = $userRepository;
        $this->dealerOptionsService = $dealerOptionsService;
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
     * Retrieve dealer user
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function show(Request $request): Response {
        $getRequest = new GetDealerRequest($request->all());

        if($getRequest->validate()) {
            return $this->response->item(
                $this->userRepository->get($request->all()),
                new UserTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Activate Dealer Classifieds
     * @param Request $request
     * @param bool $activate
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function updateDealerClassifieds(Request $request): Response {
        $getRequest = new DealerClassifiedsRequest($request->all());
        if($getRequest->validate()) {
            if ($getRequest->active) {
                if ($this->dealerOptionsService->activateDealerClassifieds($request->dealer_id)) {
                    return $this->successResponse();
                }
            } else {
                if ($this->dealerOptionsService->deactivateDealerClassifieds($request->dealer_id)) {
                    return $this->successResponse();
                }
            }
        }

        return $this->response->errorBadRequest();
    }
}

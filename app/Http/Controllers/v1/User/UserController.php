<?php

namespace App\Http\Controllers\v1\User;

use App\Domains\InteractionIntegration\Permissions\InteractionIntegrationFeature;
use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DealerClassifiedsRequest;
use App\Http\Requests\User\GetDealerRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\ListUserByNameRequest;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\DealerOptionsService;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerOfTTTransformer;
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
        $this->middleware('setDealerIdOnRequest')->except(['create', 'listByName']);
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
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     * @throws \Exception
     */
    public function updateDealerClassifieds(Request $request): Response
    {
        $getRequest = new DealerClassifiedsRequest($request->all());

        $fields = (object) [
            'subscription' => 'clsf_active',
            'active' => $getRequest->active
        ];

        if (!$getRequest->validate()) {
            return $this->response->errorBadRequest();
        }

        $this->dealerOptionsService->manageDealerSubscription(
            $request->dealer_id,
            $fields
        );

        return $this->successResponse();
    }

    /**
     * @throws NoObjectTypeSetException
     * @throws NoObjectIdValueSetException
     */
    public function listByName(Request $request): Response
    {
        $request = new ListUserByNameRequest($request->all());

        $request->validate();

        return $this->response->collection(
            $this->userRepository->getByName($request->input('name')),
            new UserTransformer()
        );
    }

    /**
     * @throws NoObjectTypeSetException
     * @throws NoObjectIdValueSetException
     */
    public function listOfTTDealers(Request $request): Response
    {
        return $this->response->collection(
            $this->userRepository->getClsfActiveUsers(),
            new DealerOfTTTransformer()
        );
    }
}

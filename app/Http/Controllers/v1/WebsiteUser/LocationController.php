<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\CreateLocationRequest;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\Integrations\TrailerCentral\Api\Users\UsersServiceInterface;
use App\Transformers\Location\TcApiResponseUserLocationTransformer;
use Dingo\Api\Http\Response;

class LocationController extends AbstractRestfulController
{
    public function __construct(
        private UsersServiceInterface $tcUserService,
        private TcApiResponseUserLocationTransformer $transformer,
        private WebsiteUserRepositoryInterface $userRepository
    ) {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function all(): Response
    {
        $user = auth('api')->user();
        $locations = $this->tcUserService->getLocations($user->tc_user_id);

        return $this->response->collection(collect($locations), $this->transformer);
    }

    public function create(CreateRequestInterface $request)
    {
        if ($request->validate()) {
            $user = auth('api')->user();
            $attributes = array_merge($request->all(), [
                'dealer_id' => $user->tc_user_id,
            ]);

            if ($user->tc_user_location_id) {
                $tcLocation = $this->tcUserService->updateLocation($user->tc_user_location_id, $attributes);
            } else {
                $tcLocation = $this->tcUserService->createLocation($attributes);
                $this->userRepository->update($user->id, [
                    'tc_user_location_id' => $tcLocation->id,
                ]);
            }

            return $this->response->item($tcLocation, $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(CreateLocationRequest::class);
        });
    }
}

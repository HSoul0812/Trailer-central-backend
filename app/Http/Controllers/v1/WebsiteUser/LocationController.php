<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\DTOs\User\TcApiResponseUserLocation;
use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\CreateLocationRequest;
use App\Services\Integrations\TrailerCentral\Api\Users\UsersServiceInterface;
use App\Transformers\Location\TcApiResponseUserLocationTransformer;

class LocationController extends AbstractRestfulController
{

    public function __construct(
        private UsersServiceInterface $tcUserService,
        private TcApiResponseUserLocationTransformer $transformer
    )
    {
    }

    public function index(IndexRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        if($request->validate()) {
            $user = auth('api')->user();
            $attributes = array_merge($request->all(), [
                'dealer_id' => $user->tc_user_id
            ]);

            $tcLocation = $this->tcUserService->createLocation($attributes);
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

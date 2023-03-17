<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\TrackUserRequest;
use App\Repositories\WebsiteUser\UserTrackingRepositoryInterface;
use Throwable;

class TrackController extends AbstractRestfulController
{
    public function __construct(
        private UserTrackingRepositoryInterface $userTrackingRepository
    )
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        $request->validate();

        try {
            return $this->response->created(
                content: $this->userTrackingRepository->create($request->all()),
            );
        } catch (Throwable $e) {
            $this->response->errorBadRequest($e->getMessage());
        }
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
            return inject_request_data(TrackUserRequest::class);
        });
    }
}

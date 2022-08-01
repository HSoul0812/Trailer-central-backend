<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\AuthenticateUserRequest;
use App\Transformers\WebsiteUser\WebsiteUserTransformer;

class ProfileController extends AbstractRestfulController
{
    public function __construct(
        private WebsiteUserTransformer $transformer
    )
    {
        parent::__construct();
    }
    public function get(): \Dingo\Api\Http\Response
    {
        $user = auth('api')->user();
        return $this->response->item($user, $this->transformer);
    }

    public function index(IndexRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        throw new NotImplementedException();
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
    }
}

<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\AuthenticateUserRequest;
use App\Http\Requests\WebsiteUser\RegisterUserRequest;
use App\Services\WebsiteUser\AuthServiceInterface;
use App\Transformers\WebsiteUser\WebsiteUserTransformer;
use Dingo\Api\Http\Request;

class AuthController extends AbstractRestfulController
{
    public function __construct(
        private AuthServiceInterface $authService,
        private WebsiteUserTransformer $transformer
    )
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        if($request->validate()) {
            $token = $this->authService->authenticate($request->all());
            return $this->response->array([
                'token' => $token
            ]);
        }
        return $this->response->errorBadRequest();
    }

    public function create(CreateRequestInterface $request)
    {
        if($request->validate()) {
            $user = $this->authService->register($request->all());
            return $this->response->item($user, $this->transformer);
        }
        return $this->response->errorBadRequest();
    }

    public function social(string $social, Request $request) {
        return $this->authService->authenticateSocial($social);
    }

    public function socialCallback(string $social, Request $request) {
        $this->authService->authenticateSocialCallback($social);
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
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(AuthenticateUserRequest::class);
        });

        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(RegisterUserRequest::class);
        });
    }
}

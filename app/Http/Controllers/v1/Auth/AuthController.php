<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\RegisterUserRequest;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\Auth\AuthServiceInterface;
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
        // TODO: Implement index() method.
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
        // TODO: Implement show() method.
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        // TODO: Implement update() method.
    }

    public function destroy(int $id)
    {
        // TODO: Implement destroy() method.
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(RegisterUserRequest::class);
        });
    }
}

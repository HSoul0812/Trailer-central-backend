<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Auth\AuthServiceInterface;
use Dingo\Api\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends AbstractRestfulController
{
    public function __construct(private AuthServiceInterface $authService)
    {}

    public function index(IndexRequestInterface $request)
    {
        // TODO: Implement index() method.
    }

    public function create(CreateRequestInterface $request)
    {
        // TODO: Implement create() method.
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

    }
}

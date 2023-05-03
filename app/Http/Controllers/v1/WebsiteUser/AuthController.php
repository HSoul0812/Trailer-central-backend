<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\AuthenticateRequestInterface;
use App\Http\Requests\WebsiteUser\AuthenticateUserRequest;
use App\Http\Requests\WebsiteUser\GetUserProfileRequest;
use App\Http\Requests\WebsiteUser\RegisterUserRequest;
use App\Http\Requests\WebsiteUser\UpdateUserRequest;
use App\Services\WebsiteUser\AuthServiceInterface;
use App\Transformers\WebsiteUser\WebsiteUserTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class AuthController extends AbstractRestfulController
{
    public function __construct(
        private AuthServiceInterface $authService,
        private WebsiteUserTransformer $transformer
    ) {
        parent::__construct();
    }

    public function authenticate(AuthenticateRequestInterface $request)
    {
        $request->validate();

        $token = $this->authService->authenticate($request->all());

        return $this->respondWithJwtToken($token, [
            'user' => $this->transformer->transform(auth('api')->user()),
        ]);
    }

    public function social(string $social, Request $request)
    {
        $callback = $request->input('callback');

        return $this->authService->authenticateSocial($social, $callback);
    }

    public function socialCallback(string $social, Request $request)
    {
        $params = [];
        parse_str($request->input('state'), $params);
        $callback = $params['callback'] ?? config('auth.login_url');

        $token = $this->authService->authenticateSocialCallback($social);

        return redirect("$callback?token=$token");
    }

    public function create(CreateRequestInterface $request)
    {
        if ($request->validate()) {
            $user = $this->authService->register($request->all());

            return $this->response->item($user, $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function getProfile(IndexRequestInterface $request): Response
    {
        $user = auth('api')->user();

        return $this->response->item($user, $this->transformer);
    }

    public function index(IndexRequestInterface $request)
    {
        // TODO: Implement index() method.
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        // TODO: Implement update() method.
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    public function jwtRefreshToken(): Response
    {
        return $this->respondWithJwtToken(auth('api')->refresh());
    }

    public function jwtLogout(): Response
    {
        auth('api')->logout();

        return $this->response->array(['message' => 'Token invalidated!']);
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithJwtToken(string $token, array $extras = []): Response
    {
        $response = array_merge([
            'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], $extras);

        return $this->response->array($response);
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(GetUserProfileRequest::class);
        });

        app()->bind(AuthenticateRequestInterface::class, function () {
            return inject_request_data(AuthenticateUserRequest::class);
        });

        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(RegisterUserRequest::class);
        });

        app()->bind(UpdateRequestInterface::class, function () {
            return inject_request_data(UpdateUserRequest::class);
        });
    }
}

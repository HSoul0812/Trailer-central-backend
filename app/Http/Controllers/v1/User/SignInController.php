<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\PasswordResetServiceInterface;
use App\Http\Requests\User\FinishPasswordResetRequest;
use App\Http\Requests\User\SignInRequest;
use App\Http\Requests\User\StartPasswordResetRequest;
use App\Models\User\AuthToken;
use App\Http\Requests\User\GetDetailsRequest;
use Dingo\Api\Http\Request;
use App\Transformers\User\UserSignInTransformer;
use App\Transformers\User\UserTransformer;

class SignInController extends RestfulController {
    
    protected $users;
    
    protected $passwordResetService;
    
    protected $transformer;
    
    public function __construct(UserRepositoryInterface $userRepo, PasswordResetServiceInterface $passwordResetService)
    {
        $this->users = $userRepo;
        $this->passwordResetService = $passwordResetService;
        $this->transformer = new UserSignInTransformer();
    }
    
    public function details(Request $request)
    {
        $accessToken = $request->header('access-token');
        $request = new GetDetailsRequest($request->all());
        if ($request->validate()) {
            $authToken = AuthToken::where('access_token', $accessToken)->firstOrFail();
            
            if (isset($authToken->user->user)) {
                return $this->response->item($authToken->user->user, new UserTransformer());
            }
            
            return $this->response->item($authToken->user, new UserTransformer());
        }
        return $this->response->errorBadRequest();
    }
    
    public function signIn(Request $request)
    {
        $request = new SignInRequest($request->all());
        if ($request->validate()) {
            try {
                return $this->response->item($this->users->findUserByEmailAndPassword($request->email, $request->password), $this->transformer);
            } catch (\Exception $ex) {
                return $this->response->errorBadRequest();
            }            
        }
        return $this->response->errorBadRequest();
    }
    
    public function initPasswordReset(Request $request)
    {
        $request = new StartPasswordResetRequest($request->all());
        if ($request->validate()) {
            try {
                $this->passwordResetService->initReset($request->email);
            } catch (\Exception $ex) {
                // Return created anyway
            }            
            return $this->response->created();
            
        }
        return $this->response->errorBadRequest();
    }
    
    public function finishPasswordReset(Request $request)
    {
        $request = new FinishPasswordResetRequest($request->all());
        if ($request->validate()) {
            try {
                $this->passwordResetService->finishReset($request->code, $request->password);
            } catch (\Exception $ex) {
                return $this->response->errorForbidden();
            }
            
            return $this->response->created();
            
        }
        return $this->response->errorBadRequest();
    }
}

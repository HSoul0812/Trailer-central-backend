<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\UserRepositoryInterface;
use App\Http\Requests\User\SignInRequest;
use Dingo\Api\Http\Request;
use App\Transformers\User\UserSignInTransformer;

class SignInController extends RestfulController {
    
    protected $users;
    
    protected $transformer;
    
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->users = $userRepo;
        $this->transformer = new UserSignInTransformer();
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
}

<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\UserRepositoryInterface;
use App\Http\Requests\User\SignInRequest;

class SigninController extends RestfulController {
    
    protected $users;
    
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->users = $userRepo;
    }
    
    public function signIn(SignInRequest $request)
    {
        die('123');
    }
}

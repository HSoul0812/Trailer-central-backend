<?php

namespace App\Repositories\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerUser;

class UserRepository implements UserRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function findUserByEmailAndPassword($email, $password) {
        $user = User::where('email', $email)->first();        
        if ( $user && ( $user->password == crypt($password, $user->salt) ) ) {
            return $user;
        }
        
        // Check dealer users
        $dealerUser = DealerUser::where('email', $email)->first(); 
        
        if ( $dealerUser && ( $dealerUser->password == crypt($password, $dealerUser->salt) ) ) {
            return $dealerUser;
        }
        
        throw new ModelNotFoundException;
    }

}

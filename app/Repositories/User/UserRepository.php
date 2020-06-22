<?php

namespace App\Repositories\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $user = User::where('email', $email)->firstOrFail();
        if ( $user->password == crypt($password, $user->salt) ) {
            return $user;
        }
        throw new ModelNotFoundException;
    }

}

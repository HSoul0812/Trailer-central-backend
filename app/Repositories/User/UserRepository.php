<?php

namespace App\Repositories\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\User;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerUser;

class UserRepository implements UserRepositoryInterface {
    use Transaction;

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        return User::findOrFail($params['dealer_id']);
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

    public function getDmsActiveUsers() {
        return User::where('is_dms_active', 1)->get();
    }

    public function setAdminPasswd($dealerId, $passwd)
    {
        return User::where('dealer_id', $dealerId)->update([
            'admin_passwd' => sha1($passwd)
        ]);
    }

}

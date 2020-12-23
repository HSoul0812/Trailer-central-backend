<?php

namespace App\Repositories\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\User;
use App\Models\User\NewDealerUser;
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

    public function getDmsActiveUsers() {
        return User::where('is_dms_active', 1)->get();
    }

    /**
     * Get CRM Active Users
     * 
     * @param array $params
     * @return Collection of NewDealerUser
     */
    public function getCrmActiveUsers($params) {
        // Initialize Query for NewDealerUser
        $dealers = NewDealerUser::has('activeCrmUser')->with('user');

        // Has Sales People?
        if(!empty($params['has'])) {
            foreach($params['has'] as $has) {
                $dealers = $dealers->has($has);
            }
        }

        // Add Where Dealer ID
        var_dump($params);
        if(!empty($params['dealer_id'])) {
            $dealers = $dealers->where('id', $params['dealer_id']);
        }
        // Bounds Exist?!
        else if(!empty($params['bound_lower']) && !empty($params['bound_upper'])) {
            $dealers = $dealers->where('id', '>=', $params['bound_lower'])
                               ->where('id', '<=', $params['bound_upper']);
        }
        // Only Lower Bound Exists!
        else if(!empty($params['bound_lower'])) {
            $dealers = $dealers->where('id', '>=', $params['bound_lower']);
        }
        echo $dealers->toSql();

        // Return Results
        return $dealers->get();
    }

    public function setAdminPasswd($dealerId, $passwd)
    {
        return User::where('dealer_id', $dealerId)->update([
            'admin_passwd' => sha1($passwd)
        ]);
    }

}

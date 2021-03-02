<?php

namespace App\Repositories\User;

use App\Exceptions\NotImplementedException;
use App\Models\User\User;
use App\Models\User\NewDealerUser;
use App\Services\Common\EncrypterServiceInterface;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerUser;

class UserRepository implements UserRepositoryInterface {
    use Transaction;

    /**
     * @var EncrypterServiceInterface
     */
    private $encrypterService;

    /**
     * @param  EncrypterServiceInterface  $encrypterService
     */
    public function __construct(EncrypterServiceInterface $encrypterService)
    {
        $this->encrypterService = $encrypterService;
    }

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return User
     */
    public function get($params): User
    {
        return User::findOrFail($params['dealer_id']);
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * @param  string  $email
     * @param  string  $password
     * @return User|DealerUser
     *
     * @throws ModelNotFoundException when a dealer or user-belonging-to-a-dealer is not found
     */
    public function findUserByEmailAndPassword($email, $password) {
        $user = User::where('email', $email)->first();
        if ($user && $this->passwordMatch($user->password, $password, $user->salt)) {
            return $user;
        }

        // Check dealer users
        $dealerUser = DealerUser::where('email', $email)->first();

        if ($dealerUser && $this->passwordMatch($dealerUser->password, $password, $dealerUser->salt)) {
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
        if(!empty($params['dealer_id'])) {
            $dealers = $dealers->where('id', $params['dealer_id']);
        }
        // Bounds Exist?!
        else if($params['bound_lower'] !== NULL && !empty($params['bound_upper'])) {
            $dealers = $dealers->where('id', '>=', $params['bound_lower'])
                               ->where('id', '<=', $params['bound_upper']);
        }
        // Only Lower Bound Exists!
        else if($params['bound_lower'] !== NULL) {
            $dealers = $dealers->where('id', '>=', $params['bound_lower']);
        }

        // Return Results
        return $dealers->get();
    }

    public function setAdminPasswd($dealerId, $passwd)
    {
        return User::where('dealer_id', $dealerId)->update([
            'admin_passwd' => sha1($passwd)
        ]);
    }

    private function passwordMatch(string $expectedPassword, string $password, string $salt): bool
    {
        return $expectedPassword === $this->encrypterService->encryptBySalt($password, $salt);
    }
}
